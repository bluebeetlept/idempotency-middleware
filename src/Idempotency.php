<?php

declare(strict_types = 1);

namespace BlueBeetle\IdempotencyMiddleware;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Psr\SimpleCache\InvalidArgumentException;
use Ramsey\Uuid\Rfc4122\Fields as UuidFields;
use Ramsey\Uuid\UuidFactory;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

readonly class Idempotency
{
    public function __construct(
        private Config $config,
        private Cache $cache,
        private ResponseFactory $response,
        private UuidFactory $uuidFactory,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $this->requestIsIdempotent($request)) {
            return $next($request);
        }

        if (! $this->isValidIdempotentKey($request)) {
            throw new IdempotencyException(
                message: 'The given idempotency key is invalid. Please ensure the key is a valid UUID value.',
            );
        }

        if ($this->requestIsRepeated($request)) {
            if ($this->requestContentDoNotMatch($request)) {
                throw new IdempotencyException(
                    message: 'A resource has been created with this idempotency key but with different content.',
                );
            }

            if ($this->requestPathDoNotMatch($request)) {
                throw new IdempotencyException(
                    message: 'A resource has been created with this idempotency key but on a different endpoint.',
                );
            }

            $cache = $this->cache->get($this->getCacheKey($request));

            return $this->response->make($cache['body'], $cache['status'], array_merge($cache['headers'], [
                $this->getMainHeaderName() => $this->getIdempotencyKey($request),
                $this->getRepeatedHeaderName() => $this->getIdempotencyKey($request),
            ]));
        }

        return $this->processRequest($request, $next);
    }

    private function getIdempotencyKey(Request $request): string
    {
        return $request->header($this->getMainHeaderName());
    }

    private function getCacheKey(Request $request): string
    {
        return $this->getIdempotencyKey($request);
    }

    private function isValidIdempotentKey(Request $request): bool
    {
        try {
            $uuidString = $this->getIdempotencyKey($request);

            $uuid = $this->uuidFactory->fromString($uuidString);

            $uuidFields = new UuidFields($uuid->getBytes());

            return $uuidFields->getVersion() === 4;
        } catch (Throwable) {
            return false;
        }
    }

    private function requestIsIdempotent(Request $request): bool
    {
        $httpMethods = $this->config->get('bluebeetle.idempotency.http_methods', ['POST']);

        return $request->hasHeader($this->getMainHeaderName()) && in_array($request->getMethod(), $httpMethods, true);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function requestIsRepeated(Request $request): bool
    {
        return $this->cache->has($this->getCacheKey($request));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function requestContentDoNotMatch(Request $request): bool
    {
        $cache = $this->cache->get($this->getCacheKey($request));

        return $request->getContent() !== $cache['request_body'];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function requestPathDoNotMatch(Request $request): bool
    {
        $cache = $this->cache->get($this->getCacheKey($request));

        return $request->path() !== $cache['path'];
    }

    private function processRequest(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($response->getStatusCode() !== Response::HTTP_UNPROCESSABLE_ENTITY) {
            $this->cache->put($this->getCacheKey($request), [
                'body' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $response->headers->all(),
                'path' => $request->path(),
                'request_body' => $request->getContent(),
            ], CarbonImmutable::now()->addMinutes($this->getExpirationTime()));
        }

        $response->headers->set(
            key: $this->getMainHeaderName(),
            values: $this->getIdempotencyKey($request),
        );

        return $response;
    }

    private function getMainHeaderName(): string
    {
        return $this->config->get('bluebeetle.idempotency.main_header_name', 'Idempotency-Key');
    }

    private function getRepeatedHeaderName(): string
    {
        return $this->config->get('bluebeetle.idempotency.repeated_header_name', 'Idempotent-Replayed');
    }

    private function getExpirationTime(): int
    {
        return (int) $this->config->get('bluebeetle.idempotency.expiration_time', 360);
    }
}
