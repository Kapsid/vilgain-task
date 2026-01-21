<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class EmailAlreadyExistsException extends HttpException
{
    public function __construct(string $email)
    {
        parent::__construct(
            Response::HTTP_CONFLICT,
            \sprintf('Email "%s" is already registered.', $email),
        );
    }
}
