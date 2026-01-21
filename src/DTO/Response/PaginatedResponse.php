<?php

declare(strict_types=1);

namespace App\DTO\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Paginated response wrapper')]
final readonly class PaginatedResponse
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(
        #[OA\Property(description: 'Array of items')]
        public array $data,
        #[OA\Property(description: 'Total number of items')]
        public int $total,
        #[OA\Property(description: 'Number of items per page')]
        public int $limit,
        #[OA\Property(description: 'Number of items skipped')]
        public int $offset,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function create(array $data, int $total, int $limit, int $offset): self
    {
        return new self($data, $total, $limit, $offset);
    }
}
