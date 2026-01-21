<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class EntityNotFoundException extends HttpException
{
    public function __construct(string $entity, int|string $id)
    {
        parent::__construct(
            Response::HTTP_NOT_FOUND,
            \sprintf('%s with ID "%s" not found.', $entity, $id),
        );
    }
}
