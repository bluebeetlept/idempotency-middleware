<?php

declare(strict_types = 1);

namespace BlueBeetle\IdempotencyMiddleware\Tests;

use BlueBeetle\IdempotencyMiddleware\IdempotencyServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * @param mixed $app
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            IdempotencyServiceProvider::class,
        ];
    }
}
