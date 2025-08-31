<?php

declare(strict_types=1);

namespace App\Service;

readonly class PaginatedResult
{
    public function __construct(
        public array $data,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->total / $this->limit);
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->getTotalPages();
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'pagination' => [
                'page' => $this->page,
                'limit' => $this->limit,
                'total' => $this->total,
                'totalPages' => $this->getTotalPages(),
                'hasNext' => $this->hasNextPage(),
                'hasPrevious' => $this->hasPreviousPage(),
            ]
        ];
    }
}
