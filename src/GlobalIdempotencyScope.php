<?php

declare(strict_types = 1);

namespace BlueBeetle\IdempotencyMiddleware;

use BlueBeetle\IdempotencyMiddleware\Contracts\IdempotencyScope;
use Illuminate\Http\Request;

/**
 * The default scope: a single, global idempotency namespace. Applications that serve
 * multiple tenants (or a live/test split) should bind their own {@see IdempotencyScope}
 * to partition the cache per tenant.
 */
final class GlobalIdempotencyScope implements IdempotencyScope
{
    public function resolve(Request $request): string
    {
        return '';
    }
}
