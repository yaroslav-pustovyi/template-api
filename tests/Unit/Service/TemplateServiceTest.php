<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Document\Category;
use App\Document\Template;
use App\Dto\CreateTemplateRequest;
use App\Repository\CategoryRepository;
use App\Repository\TemplateRepository;
use App\Service\TemplateService;
use Doctrine\ODM\MongoDB\DocumentManager;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TemplateServiceTest extends TestCase
{
    private TemplateService $templateService;
    private TemplateRepository $templateRepository;
    private CategoryRepository $categoryRepository;
    private DocumentManager $documentManager;

    protected function setUp(): void
    {
        $this->templateRepository = $this->createMock(TemplateRepository::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->documentManager = $this->createMock(DocumentManager::class);

        $this->templateService = new TemplateService(
            $this->templateRepository,
            $this->categoryRepository,
            $this->documentManager
        );
    }

    #[DataProvider('validCreateRequestProvider')]
    public function testCreateWithValidData(
        string $name,
        string $displayName,
        string $categoryId,
        array $preview,
        array $templateData
    ): void {
        $request = new CreateTemplateRequest();
        $request->name = $name;
        $request->displayName = $displayName;
        $request->categoryId = $categoryId;
        $request->preview = $preview;
        $request->templateData = $templateData;

        $category = $this->createMock(Category::class);

        $this->templateRepository
            ->expects($this->once())
            ->method('existsByName')
            ->with($name)
            ->willReturn(false)
        ;

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($category)
        ;

        $this->documentManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Template::class))
        ;

        $result = $this->templateService->create($request);

        $this->assertEquals($name, $result->getName());
        $this->assertEquals($displayName, $result->getDisplayName());
        $this->assertEquals($preview, $result->getPreview());
        $this->assertEquals($templateData, $result->getTemplateData());
        $this->assertEquals($category, $result->getCategory());
    }

    public static function validCreateRequestProvider(): array
    {
        return [
            'Basic template' => [
                'name' => 'Basic Template',
                'displayName' => 'Basic Template Display',
                'categoryId' => 'category123',
                'preview' => [],
                'templateData' => []
            ],
            'Template with preview' => [
                'name' => 'Template with Preview',
                'displayName' => 'Template with Preview Display',
                'categoryId' => 'category456',
                'preview' => [
                    'aspectRatio' => 1.0,
                    'imageURL' => 'https://example.com/preview.png'
                ],
                'templateData' => []
            ],
            'AI Filter template' => [
                'name' => 'AI Filter Template',
                'displayName' => 'AI Filter Display',
                'categoryId' => 'categoryAI',
                'preview' => [
                    'aspectRatio' => 0.75,
                    'imageURL' => 'https://example.com/ai-preview.png'
                ],
                'templateData' => [
                    'aiFilter' => [
                        'aiFilter' => [
                            'userPrompt' => [
                                'title' => 'Create your avatar',
                                'inputFields' => [
                                    [
                                        'id' => 'field1',
                                        'type' => 'textField',
                                        'caption' => 'Your style',
                                        'placeholder' => 'Cartoon, Realistic',
                                        'maxLength' => 100
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Image to Video template' => [
                'name' => 'Video Template',
                'displayName' => 'Video Template Display',
                'categoryId' => 'categoryVideo',
                'preview' => [
                    'aspectRatio' => 1.77,
                    'imageURL' => 'https://example.com/video-preview.png',
                    'videoURL' => 'https://example.com/demo.mp4'
                ],
                'templateData' => [
                    'imageToVideo' => [
                        'imageToVideo' => [
                            'categoryName' => 'Product Shots',
                            'categoryDisplayName' => 'Product shots',
                            'backgrounds' => [
                                [
                                    'name' => 'bg_studio',
                                    'originalImage' => 'https://example.com/studio.png',
                                    'previewImage' => 'https://example.com/studio-preview.png'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function testCreateThrowsExceptionWhenTemplateNameExists(): void
    {
        $request = new CreateTemplateRequest();
        $request->name = 'Existing Template';
        $request->displayName = 'Existing Display';
        $request->categoryId = 'category123';

        $this->templateRepository
            ->expects($this->once())
            ->method('existsByName')
            ->with('Existing Template')
            ->willReturn(true)
        ;

        $this->categoryRepository
            ->expects($this->never())
            ->method('findById')
        ;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Template with name 'Existing Template' already exists");

        $this->templateService->create($request);
    }

    public function testCreateThrowsExceptionWhenCategoryNotFound(): void
    {
        $request = new CreateTemplateRequest();
        $request->name = 'New Template';
        $request->displayName = 'New Display';
        $request->categoryId = 'nonexistent123';

        $this->templateRepository
            ->expects($this->once())
            ->method('existsByName')
            ->with('New Template')
            ->willReturn(false)
        ;

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with('nonexistent123')
            ->willReturn(null)
        ;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Category with id 'nonexistent123' not found");

        $this->templateService->create($request);
    }

    #[DataProvider('invalidTemplateNameProvider')]
    public function testCreateWithDuplicateNames(string $existingName, string $newName): void
    {
        $request = new CreateTemplateRequest();
        $request->name = $newName;
        $request->displayName = 'Display Name';
        $request->categoryId = 'category123';

        $this->templateRepository
            ->expects($this->once())
            ->method('existsByName')
            ->with($newName)
            ->willReturn(true)
        ;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Template with name '{$newName}' already exists");

        $this->templateService->create($request);
    }

    public static function invalidTemplateNameProvider(): array
    {
        return [
            'Exact duplicate' => ['Template Name', 'Template Name'],
            'Case sensitive duplicate' => ['Template Name', 'Template Name'],
            'Special characters' => ['Template @#$', 'Template @#$'],
            'Long name duplicate' => [
                'This is a very long template name that exceeds normal length',
                'This is a very long template name that exceeds normal length'
            ]
        ];
    }
}
