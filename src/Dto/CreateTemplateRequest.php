<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTemplateRequest
{
    #[Assert\NotBlank(message: 'Name is required')]
    #[Assert\Length(max: 100, maxMessage: 'Name must not exceed {{ limit }} characters')]
    public $name;

    #[Assert\NotBlank(message: 'Display name is required')]
    #[Assert\Length(max: 100, maxMessage: 'Display name must not exceed {{ limit }} characters')]
    public $displayName;

    #[Assert\NotBlank(message: 'Category ID is required')]
    public $categoryId;

    #[Assert\Type(type: 'array', message: 'Preview must be an array')]
    public $preview;

    #[Assert\Type(type: 'array', message: 'Template data must be an array')]
    public $templateData;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->name = $data['name'] ?? '';
        $dto->displayName = $data['displayName'] ?? '';
        $dto->categoryId = $data['categoryId'] ?? '';
        $dto->preview = $data['preview'] ?? [];
        $dto->templateData = $data['templateData'] ?? [];

        return $dto;
    }
}
