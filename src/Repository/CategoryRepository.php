<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Category;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class CategoryRepository
{
    private DocumentRepository $repository;

    public function __construct(private readonly DocumentManager $documentManager)
    {
        $this->repository = $this->documentManager->getRepository(Category::class);
    }

    public function findById(string $id): ?Category
    {
        return $this->repository->find($id);
    }
}
