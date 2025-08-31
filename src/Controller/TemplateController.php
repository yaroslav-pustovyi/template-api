<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateTemplateRequest;
use App\Service\TemplateService;
use Doctrine\ODM\MongoDB\DocumentManager;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/v1/templates')]
class TemplateController extends AbstractController
{
    public function __construct(
        private readonly TemplateService $templateService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly DocumentManager $documentManager
    ) {
    }

    #[Route('', name: 'templates_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $categoryId = $request->query->get('categoryId');
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = min(100, max(1, (int) $request->query->get('perPage', 20)));

        if ($categoryId) {
            $result = $this->templateService->findByCategory($categoryId, $page, $perPage);
        } else {
            $result = $this->templateService->findAll($page, $perPage);
        }

        $templates = $this->serializer->normalize($result->data, 'json', [
            'groups' => ['template:list']
        ]);

        return $this->json([
            'data' => $templates,
            'meta' => [
                'page' => $result->page,
                'perPage' => $result->limit,
                'total' => $result->total,
                'totalPages' => $result->getTotalPages(),
                'hasNext' => $result->hasNextPage(),
                'hasPrevious' => $result->hasPreviousPage()
            ]
        ]);
    }

    #[Route('', name: 'templates_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json(['error' => 'Invalid JSON'], 400);
            }

            $createRequest = CreateTemplateRequest::fromArray($data);
            $violations = $this->validator->validate($createRequest);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                return $this->json(['error' => 'Validation failed', 'violations' => $errors], 400);
            }

            $template = $this->templateService->create($createRequest);
            $this->documentManager->flush();

            $templateData = $this->serializer->normalize($template, 'json', [
                'groups' => ['template:read']
            ]);

            return $this->json($templateData, 201);
        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'templates_delete', methods: ['DELETE'])]
    public function delete(string $id): Response
    {
        $template = $this->templateService->findById($id);
        if (!$template) {
            return new Response('', 404);
        }

        $this->templateService->delete($template);
        $this->documentManager->flush();

        return new Response('', 204);
    }
}
