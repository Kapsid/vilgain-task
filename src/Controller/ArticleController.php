<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\CreateArticleRequest;
use App\DTO\Request\UpdateArticleRequest;
use App\DTO\Response\ArticleResponse;
use App\DTO\Response\PaginatedResponse;
use App\Entity\User;
use App\Security\Voter\ArticleVoter;
use App\Service\ArticleService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/articles')]
#[OA\Tag(name: 'Articles')]
final class ArticleController extends AbstractController
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {
    }

    #[Route('', name: 'articles_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get list of all articles',
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 50)),
            new OA\Parameter(name: 'offset', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 0)),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Paginated list of articles',
                content: new OA\JsonContent(ref: new Model(type: PaginatedResponse::class)),
            ),
        ],
    )]
    public function list(Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        $articles = $this->articleService->findAll($limit, $offset);
        $total = $this->articleService->countAll();

        return $this->json(PaginatedResponse::create(
            array_map(fn ($article) => ArticleResponse::fromEntity($article), $articles),
            $total,
            $limit,
            $offset,
        ));
    }

    #[Route('/{id}', name: 'articles_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(
        summary: 'Get article by ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Article details',
                content: new OA\JsonContent(ref: new Model(type: ArticleResponse::class)),
            ),
            new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Article not found'),
        ],
    )]
    public function show(int $id): JsonResponse
    {
        $article = $this->articleService->findByIdOrFail($id);

        return $this->json(ArticleResponse::fromEntity($article));
    }

    #[Route('', name: 'articles_create', methods: ['POST'])]
    #[IsGranted('ROLE_AUTHOR')]
    #[OA\Post(
        summary: 'Create a new article (Author or Admin only)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CreateArticleRequest::class)),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: 'Article created',
                content: new OA\JsonContent(ref: new Model(type: ArticleResponse::class)),
            ),
            new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'Validation error'),
            new OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized'),
            new OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied'),
        ],
    )]
    public function create(
        #[MapRequestPayload] CreateArticleRequest $request,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $article = $this->articleService->createArticle($request, $user);

        return $this->json(ArticleResponse::fromEntity($article), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'articles_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Put(
        summary: 'Update article (Owner or Admin only)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: UpdateArticleRequest::class)),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Article updated',
                content: new OA\JsonContent(ref: new Model(type: ArticleResponse::class)),
            ),
            new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'Validation error'),
            new OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized'),
            new OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied'),
            new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Article not found'),
        ],
    )]
    public function update(
        int $id,
        #[MapRequestPayload] UpdateArticleRequest $request,
    ): JsonResponse {
        $article = $this->articleService->findByIdOrFail($id);
        $this->denyAccessUnlessGranted(ArticleVoter::EDIT, $article);

        $article = $this->articleService->updateArticle($article, $request);

        return $this->json(ArticleResponse::fromEntity($article));
    }

    #[Route('/{id}', name: 'articles_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Delete(
        summary: 'Delete article (Owner or Admin only)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: Response::HTTP_NO_CONTENT, description: 'Article deleted'),
            new OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized'),
            new OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied'),
            new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Article not found'),
        ],
    )]
    public function delete(int $id): JsonResponse
    {
        $article = $this->articleService->findByIdOrFail($id);
        $this->denyAccessUnlessGranted(ArticleVoter::DELETE, $article);

        $this->articleService->deleteArticle($article);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
