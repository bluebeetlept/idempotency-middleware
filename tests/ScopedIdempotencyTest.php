<?php

declare(strict_types = 1);

namespace BlueBeetle\IdempotencyMiddleware\Tests;

use BlueBeetle\IdempotencyMiddleware\Contracts\IdempotencyScope;
use BlueBeetle\IdempotencyMiddleware\Idempotency;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Ramsey\Uuid\Uuid;

class ScopedIdempotencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Partition the idempotency cache by an "X-Tenant" request header.
        $this->app?->bind(IdempotencyScope::class, fn () => new class() implements IdempotencyScope {
            public function resolve(Request $request): string
            {
                return $request->header('X-Tenant', '');
            }
        });
    }

    private function makePostRequest(string $tenant, string $idempotencyKey, string $body = 'Test body'): Request
    {
        $request = Request::create('/', 'POST', [], [], [], [], $body);
        $request->headers->set('Idempotency-Key', $idempotencyKey);
        $request->headers->set('X-Tenant', $tenant);

        return $request;
    }

    #[Test]
    #[TestDox('The same idempotency key in different scopes does not collide')]
    public function test_1(): void
    {
        /** @var Idempotency $middleware */
        $middleware = $this->app?->make(Idempotency::class);

        $idempotencyKey = Uuid::uuid4()->toString();

        // Tenant 1 creates a resource with the key.
        $middleware->handle(
            request: $this->makePostRequest('1', $idempotencyKey),
            next: fn () => new Response('All is ok!', 200),
        );

        // Tenant 2 uses the SAME key; it must be treated as a fresh request, not a replay.
        $secondTenantWasHandled = false;

        /** @var Response $response */
        $response = $middleware->handle(
            request: $this->makePostRequest('2', $idempotencyKey),
            next: function () use (&$secondTenantWasHandled) {
                $secondTenantWasHandled = true;

                return new Response('All is ok!', 200);
            },
        );

        $this->assertTrue($secondTenantWasHandled);
        $this->assertFalse($response->headers->has('Idempotent-Replayed'));
        $this->assertTrue(Cache::has("1:{$idempotencyKey}"));
        $this->assertTrue(Cache::has("2:{$idempotencyKey}"));
    }

    #[Test]
    #[TestDox('The same idempotency key within the same scope is replayed')]
    public function test_2(): void
    {
        /** @var Idempotency $middleware */
        $middleware = $this->app?->make(Idempotency::class);

        $idempotencyKey = Uuid::uuid4()->toString();

        $middleware->handle(
            request: $this->makePostRequest('1', $idempotencyKey),
            next: fn () => new Response('All is ok!', 200),
        );

        /** @var Response $response */
        $response = $middleware->handle(
            request: $this->makePostRequest('1', $idempotencyKey),
            next: fn () => new Response('Should not run', 200),
        );

        $this->assertSame('All is ok!', $response->getContent());
        $this->assertTrue($response->headers->has('Idempotent-Replayed'));
        $this->assertSame($idempotencyKey, $response->headers->get('Idempotent-Replayed'));
    }
}
