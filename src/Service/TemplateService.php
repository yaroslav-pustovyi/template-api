<?php

declare(strict_types=1);

namespace App\Service;

use App\Document\Template;
use App\Dto\CreateTemplateRequest;
use App\Repository\CategoryRepository;
use App\Repository\TemplateRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use InvalidArgumentException;

class TemplateService
{
    public function __construct(
        private readonly TemplateRepository $templateRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly DocumentManager $documentManager
    ) {
    }

    public function create(CreateTemplateRequest $request): Template
    {
        if ($this->templateRepository->existsByName($request->name)) {
            throw new InvalidArgumentException("Template with name '{$request->name}' already exists");
        }

        $category = $this->categoryRepository->findById($request->categoryId);
        if ($category === null) {
            throw new InvalidArgumentException("Category with id '{$request->categoryId}' not found");
        }

        $template = new Template($category);
        $template->setName($request->name)
            ->setDisplayName($request->displayName)
            ->setPreview($request->preview)
            ->setTemplateData($request->templateData)
        ;

        $this->documentManager->persist($template);

        return $template;
    }

    public function findById(string $id): ?Template
    {
        return $this->templateRepository->findById($id);
    }

    public function findAll(int $page = 1, int $limit = 20): PaginatedResult
    {
        return $this->templateRepository->findAll($page, $limit);
    }

    public function findByCategory(string $categoryId, int $page = 1, int $limit = 20): PaginatedResult
    {
        $category = $this->categoryRepository->findById($categoryId);
        if (!$category) {
            return new PaginatedResult([], 0, $page, $limit);
        }

        return $this->templateRepository->findByCategory($category, $page, $limit);
    }

    public function delete(Template $template): void
    {
        $this->documentManager->remove($template);
    }
}
