<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Category;
use App\Document\Template;
use App\Service\PaginatedResult;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class TemplateRepository
{
    private DocumentRepository $repository;

    public function __construct(private readonly DocumentManager $documentManager)
    {
        $this->repository = $this->documentManager->getRepository(Template::class);
    }

    public function findById(string $id): ?Template
    {
        return $this->repository->find($id);
    }

    public function findAll(int $page = 1, int $limit = 20): PaginatedResult
    {
        $offset = ($page - 1) * $limit;

        $qb = $this->repository->createQueryBuilder()
            ->skip($offset)
            ->limit($limit)
            ->sort('createdAt', 'desc')
        ;

        $templates = $qb->getQuery()->execute()->toArray();
        $total = $this->repository->createQueryBuilder()
            ->count()
            ->getQuery()
            ->execute()
        ;

        return new PaginatedResult(
            data: array_values($templates),
            total: $total,
            page: $page,
            limit: $limit
        );
    }

    public function findByCategory(Category $category, int $page = 1, int $limit = 20): PaginatedResult
    {
        $offset = ($page - 1) * $limit;

        $qb = $this->repository->createQueryBuilder()
            ->field('category')->references($category)
            ->skip($offset)
            ->limit($limit)
            ->sort('createdAt', 'desc');

        $templates = $qb->getQuery()->execute()->toArray();

        $totalQb = $this->repository->createQueryBuilder()
            ->field('category')->references($category)
            ->count();
        $total = $totalQb->getQuery()->execute();

        return new PaginatedResult(
            data: array_values($templates),
            total: $total,
            page: $page,
            limit: $limit
        );
    }

    public function existsByName(string $name, ?string $excludeId = null): bool
    {
        $qb = $this->repository->createQueryBuilder()
            ->field('name')->equals($name)
            ->count()
        ;

        if ($excludeId) {
            $qb->field('id')->notEqual($excludeId);
        }

        return $qb->getQuery()->execute() > 0;
    }
}
