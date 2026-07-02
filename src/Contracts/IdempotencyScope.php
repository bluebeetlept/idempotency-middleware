<?php

declare(strict_types = 1);

namespace BlueBeetle\IdempotencyMiddleware\Contracts;

use Illuminate\Http\Request;

interface IdempotencyScope
{
    /**
     * A stable scope that partitions the idempotency cache for the given request,
     * typically the authenticated tenant and environment, so the same idempotency key
     * used by different tenants (or in live vs test) never collides.
     *
     * Return an empty string for a single, global scope (the default behaviour).
     */
    public function resolve(Request $request): string;
}
