<?php

declare(strict_types = 1);

namespace BlueBeetle\IdempotencyMiddleware;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class IdempotencyException extends HttpException
{
    public function __construct(string $message = '')
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message);
    }
}
