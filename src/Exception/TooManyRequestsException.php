<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class TooManyRequestsException extends HttpException
{
    public function __construct(int $retryAfter)
    {
        parent::__construct(
            Response::HTTP_TOO_MANY_REQUESTS,
            \sprintf('Too many requests. Please try again in %d seconds.', $retryAfter),
            headers: ['Retry-After' => (string) $retryAfter],
        );
    }
}
