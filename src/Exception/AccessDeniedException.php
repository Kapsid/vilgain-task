<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class AccessDeniedException extends HttpException
{
    public function __construct(string $action, string $resource)
    {
        parent::__construct(
            Response::HTTP_FORBIDDEN,
            \sprintf('You do not have permission to %s this %s.', $action, $resource),
        );
    }
}
