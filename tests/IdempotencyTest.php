<?php

declare(strict_types = 1);

namespace BlueBeetle\IdempotencyMiddleware\Tests;

use BlueBeetle\IdempotencyMiddleware\Idempotency;
use BlueBeetle\IdempotencyMiddleware\IdempotencyException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Psr\SimpleCache\InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class IdempotencyTest extends TestCase
{
    /**
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    #[Test]
    #[TestDox('A get request without the idempotency header will not trigger the middleware')]
    public function test_1(): void
    {
        /** @var Idempotency $middleware */
        $middleware = $this->app?->make(Idempotency::class);

        $request = Request::create('/', 'GET', [], [], [], [], 'Test body');

        /** @var Response $response */
        $response = $middleware->handle(
            request: $request,
            next: fn () => new Response('All is ok!', 200),
        );

        $this->assertSame('All is ok!', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Idempotency-Key'));
        $this->assertFalse($response->headers->has('Idempotent-Replayed'));
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    #[Test]
    #[TestDox('A get request with the idempotency header will not trigger the middleware')]
    public function test_2(): void
    {
        /** @var Idempotency $middleware */
        $middleware = $this->app?->make(Idempotency::class);

        $idempotencyKey = 'fake-idempotency-key';

        $request = Request::create('/', 'GET', [], [], [], [], 'Test body');
        $request->headers->set('Idempotency-Key', $idempotencyKey);

        /** @var Response $response */
        $response = $middleware->handle(
            request: $request,
            next: fn () => new Response('All is ok!', 200),
        );

        $this->assertSame('All is ok!', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse(Cache::has($idempotencyKey));
        $this->assertFalse($response->headers->has('Idempotency-Key'));
        $this->assertFalse($response->headers->has('Idempotent-Replayed'));
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    #[Test]
    #[TestDox('A post request without the idempotency header will not trigger the middleware')]
    public function test_3(): void
    {
        /** @var Idempotency $middleware */
        $middleware = $this->app?->make(Idempotency::class);

        $request = Request::create('/', 'POST', [], [], [], [], 'Test body');

        /** @var Response $response */
        $response = $middleware->handle(
            request: $request,
            next: fn () => new Response('All is ok!', 200),
        );

        $this->assertSame('All is ok!', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Idempotency-Key'));
        $this->assertFalse($response->headers->has('Idempotent-Replayed'));
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    #[Test]
    #[TestDox('A post request with an invalid idempotency header will trigger an exception')]
    public function test_4(): void
    {
        $this->expectException(IdempotencyException::class);
        $this->expectExceptionMessage('The given idempotency key is invalid. Please ensure the key is a valid UUID value.');

        /** @var Idempotency $middleware */
        $middleware = $this->app?->make(Idempotency::class);

        $idempotencyKey = 'fake-idempotency-key';

        $request = Request::create('/', 'POST', [], [], [], [], 'Test body');
        $request->headers->set('Idempotency-Key', $idempotencyKey);

        $middleware->handle(
            request: $request,
            next: fn () => new Response('All is ok!', 200),
        );
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    #[Test]
    #[TestDox('A post request with a valid idempotency header will trigger the middleware')]
    public function test_5(): void
    {
        /** @var Idempotency $middleware */
        $middleware = $this->app?->make(Idempotency::class);

        $idempotencyKey = Uuid::uuid4()->toString();

        $request = Request::create('/', 'POST', [], [], [], [], 'Test body');
        $request->headers->set('Idempotency-Key', $idempotencyKey);

        /** @var Response $response */
        $response = $middleware->handle(
            request: $request,
            next: fn () => new Response('All is ok!', 200),
        );

        $this->assertSame('All is ok!', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(Cache::has($idempotencyKey));
        $this->assertTrue($response->headers->has('Idempotency-Key'));
        $this->assertFalse($response->headers->has('Idempotent-Replayed'));
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    #[Test]
    #[TestDox('Using the same idempotency key with the same content body and endpoint will trigger the middleware')]
    public function test_6(): void
    {
        /** @var Idempotency $middleware */
        $middleware = $this->app?->make(Idempotency::class);

        $idempotencyKey = Uuid::uuid4()->toString();

        $request1 = Request::create('/', 'POST', [], [], [], [], 'Test body');
        $request1->headers->set('Idempotency-Key', $idempotencyKey);

        $middleware->handle(
            request: $request1,
            next: fn () => new Response('All is ok!', 200),
        );

        $request2 = Request::create('/', 'POST', [], [], [], [], 'Test body');
        $request2->headers->set('Idempotency-Key', $idempotencyKey);

        /** @var Response $response */
        $response = $middleware->handle(
            request: $request2,
            next: fn () => new Response('All is ok!', 200),
        );

        $this->assertSame('All is ok!', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->headers->has('Idempotency-Key'));
        $this->assertTrue($response->headers->has('Idempotent-Replayed'));
        $this->assertSame($idempotencyKey, $response->headers->get('Idempotent-Replayed'));
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    #[Test]
    #[TestDox('Using the same idempotency key but different content body will trigger an exception')]
    public function test_7(): void
    {
        $this->expectException(IdempotencyException::class);
        $this->expectExceptionMessage('A resource has been created with this idempotency key but with different content.');

        /** @var Idempotency $middleware */
        $middleware = $this->app?->make(Idempotency::class);

        $idempotencyKey = Uuid::uuid4()->toString();

        $request1 = Request::create('/', 'POST', [], [], [], [], 'Test body 1');
        $request1->headers->set('Idempotency-Key', $idempotencyKey);

        $middleware->handle(
            request: $request1,
            next: fn () => new Response('All is ok!', 200),
        );

        $request2 = Request::create('/', 'POST', [], [], [], [], 'Test body 2');
        $request2->headers->set('Idempotency-Key', $idempotencyKey);

        $middleware->handle(
            request: $request2,
            next: fn () => new Response('All is ok!', 200),
        );
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    #[Test]
    #[TestDox('Using the same idempotency key with the same content body but on a different endpoint will trigger an exception')]
    public function test_8(): void
    {
        $this->expectException(IdempotencyException::class);
        $this->expectExceptionMessage('A resource has been created with this idempotency key but on a different endpoint.');

        /** @var Idempotency $middleware */
        $middleware = $this->app?->make(Idempotency::class);

        $idempotencyKey = Uuid::uuid4()->toString();

        $request1 = Request::create('/', 'POST', [], [], [], [], 'Test body');
        $request1->headers->set('Idempotency-Key', $idempotencyKey);

        $middleware->handle(
            request: $request1,
            next: fn () => new Response('All is ok!', 200),
        );

        $request2 = Request::create('/other-endpoint', 'POST', [], [], [], [], 'Test body');
        $request2->headers->set('Idempotency-Key', $idempotencyKey);

        $middleware->handle(
            request: $request2,
            next: fn () => new Response('All is ok!', 200),
        );
    }
}
