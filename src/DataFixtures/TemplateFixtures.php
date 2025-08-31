<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Document\Category;
use App\Document\Template;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Persistence\ObjectManager;

class TemplateFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $cartoonCategory = new Category();
        $cartoonCategory->setName('Cartoon')
            ->setDisplayName('Cartoon')
        ;

        $businessCategory = new Category();
        $businessCategory->setName('Business')
            ->setDisplayName('Business')
        ;

        $manager->persist($cartoonCategory);
        $manager->persist($businessCategory);

        // Create Starter Pack Template (Cartoon category)
        $starterPackTemplate = new Template($businessCategory);
        $starterPackTemplate->setName('Starter pack')
            ->setDisplayName('Starter pack')
            ->setPreview([
                'aspectRatio' => 0.75,
                'imageURL' => 'https://stage-cdn.example.com/image-to-image/templates/starter_pack_queen_3x4_v2.png'
            ])
            ->setTemplateData([
                'aiFilter' => [
                    'aiFilter' => [
                        'userPrompt' => [
                            'title' => 'What should be in your Starter Pack?',
                            'inputFields' => [
                                [
                                    'id' => '682b2f1d3708a23de50b7401',
                                    'type' => 'textField',
                                    'caption' => 'Your name or profession',
                                    'placeholder' => 'Sofi',
                                    'analyticsValue' => 'Your name or profession',
                                    'maxLength' => 200
                                ],
                                [
                                    'id' => '682b2f1d3708a23de50b7402',
                                    'type' => 'textView',
                                    'caption' => 'Items that represent you',
                                    'placeholder' => 'Laptop, headphones, book, coffee',
                                    'analyticsValue' => 'Items that represent you',
                                    'maxLength' => 800
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        // Create SMEG Blender Template (Business category)
        $smegTemplate = new Template($businessCategory);
        $smegTemplate->setName('SMEG Blender')
            ->setDisplayName('SMEG Blender')
            ->setPreview([
                'aspectRatio' => 0.5625,
                'imageURL' => 'https://stage-cdn.example.com/image-to-video/templates/68061499eaffe93c4e03ddc8.png',
                'videoURL' => 'https://stage-cdn.example.com/image-to-video/templates/67ebc87f2e0b424d76006d14.mp4'
            ])
            ->setTemplateData([
                'imageToVideo' => [
                    'imageToVideo' => [
                        'categoryName' => 'Product Shots',
                        'categoryDisplayName' => 'Product shots',
                        'backgrounds' => [
                            [
                                'name' => 'bg_oranges_01',
                                'originalImage' => 'https://stage-cdn.example.com/image-to-video/templates/67ffcbacae39954c0b0fcf37.png',
                                'previewImage' => 'https://stage-cdn.example.com/image-to-video/templates/67ffcbb4ae39954c0b0fcf38.png'
                            ],
                            [
                                'name' => 'bg_oranges_02',
                                'originalImage' => 'https://stage-cdn.example.com/image-to-video/templates/6800cc1bae39954c0b0fcf47.png',
                                'previewImage' => 'https://stage-cdn.example.com/image-to-video/templates/6800cc25ae39954c0b0fcf48.png'
                            ],
                            [
                                'name' => 'bg_oranges_03',
                                'originalImage' => 'https://stage-cdn.example.com/image-to-video/templates/6800cc34ae39954c0b0fcf49.png',
                                'previewImage' => 'https://stage-cdn.example.com/image-to-video/templates/6800cc3fae39954c0b0fcf4a.png'
                            ],
                            [
                                'name' => 'bg_oranges_04',
                                'originalImage' => 'https://stage-cdn.example.com/image-to-video/templates/6800cc42ae39954c0b0fcf4b.png',
                                'previewImage' => 'https://stage-cdn.example.com/image-to-video/templates/6800cc4fae39954c0b0fcf4c.png'
                            ]
                        ]
                    ]
                ]
            ]);

        $manager->persist($starterPackTemplate);
        $manager->persist($smegTemplate);

        // Create additional demo templates
        $this->createAdditionalTemplates($manager, $cartoonCategory, $businessCategory);

        $manager->flush();
    }

    private function createAdditionalTemplates(ObjectManager $manager, Category $cartoonCategory, Category $businessCategory): void
    {
        // Additional cartoon template
        $cartoonTemplate = new Template($cartoonCategory);
        $cartoonTemplate->setName('Avatar Creator')
            ->setDisplayName('Avatar Creator')
            ->setPreview([
                'aspectRatio' => 1.0,
                'imageURL' => 'https://example.com/avatar-preview.png'
            ])
            ->setTemplateData([
                'aiFilter' => [
                    'aiFilter' => [
                        'userPrompt' => [
                            'title' => 'Create your cartoon avatar',
                            'inputFields' => [
                                [
                                    'id' => 'avatar_001',
                                    'type' => 'textField',
                                    'caption' => 'Your style preference',
                                    'placeholder' => 'Anime, Cartoon, Realistic',
                                    'maxLength' => 100
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        // Additional business template
        $businessTemplate = new Template($businessCategory);
        $businessTemplate->setName('Product Showcase')
            ->setDisplayName('Product Showcase')
            ->setPreview([
                'aspectRatio' => 1.77,
                'imageURL' => 'https://example.com/product-showcase.png'
            ])
            ->setTemplateData([
                'imageToVideo' => [
                    'imageToVideo' => [
                        'categoryName' => 'E-commerce',
                        'categoryDisplayName' => 'E-commerce',
                        'backgrounds' => [
                            [
                                'name' => 'clean_white',
                                'originalImage' => 'https://example.com/clean-white-bg.png',
                                'previewImage' => 'https://example.com/clean-white-preview.png'
                            ]
                        ]
                    ]
                ]
            ]);

        $manager->persist($cartoonTemplate);
        $manager->persist($businessTemplate);
    }
}
