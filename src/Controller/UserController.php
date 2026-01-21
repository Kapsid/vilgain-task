<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\CreateUserRequest;
use App\DTO\Request\UpdateUserRequest;
use App\DTO\Response\PaginatedResponse;
use App\DTO\Response\UserResponse;
use App\Service\UserService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/users')]
#[OA\Tag(name: 'Users')]
#[IsGranted('ROLE_ADMIN')]
final class UserController extends AbstractController
{
    private const MAX_LIMIT = 100;

    public function __construct(
        private readonly UserService $userService,
    ) {
    }

    #[Route('', name: 'users_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get list of all users (Admin only)',
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 50)),
            new OA\Parameter(name: 'offset', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 0)),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Paginated list of users',
                content: new OA\JsonContent(ref: new Model(type: PaginatedResponse::class)),
            ),
            new OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized'),
            new OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied'),
        ],
    )]
    public function list(Request $request): JsonResponse
    {
        $limit = min($request->query->getInt('limit', 50), self::MAX_LIMIT);
        $offset = max($request->query->getInt('offset', 0), 0);

        $users = $this->userService->findAll($limit, $offset);
        $total = $this->userService->countAll();

        return $this->json(PaginatedResponse::create(
            array_map(fn ($user) => UserResponse::fromEntity($user), $users),
            $total,
            $limit,
            $offset,
        ));
    }

    #[Route('/{id}', name: 'users_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(
        summary: 'Get user by ID (Admin only)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'User details',
                content: new OA\JsonContent(ref: new Model(type: UserResponse::class)),
            ),
            new OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized'),
            new OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied'),
            new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'User not found'),
        ],
    )]
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findByIdOrFail($id);

        return $this->json(UserResponse::fromEntity($user));
    }

    #[Route('', name: 'users_create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create a new user (Admin only)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CreateUserRequest::class)),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: 'User created',
                content: new OA\JsonContent(ref: new Model(type: UserResponse::class)),
            ),
            new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'Validation error'),
            new OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized'),
            new OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied'),
            new OA\Response(response: Response::HTTP_CONFLICT, description: 'Email already exists'),
        ],
    )]
    public function create(#[MapRequestPayload] CreateUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request);

        return $this->json(UserResponse::fromEntity($user), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'users_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[OA\Put(
        summary: 'Update user (Admin only)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: UpdateUserRequest::class)),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'User updated',
                content: new OA\JsonContent(ref: new Model(type: UserResponse::class)),
            ),
            new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'Validation error'),
            new OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized'),
            new OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied'),
            new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'User not found'),
            new OA\Response(response: Response::HTTP_CONFLICT, description: 'Email already exists'),
        ],
    )]
    public function update(int $id, #[MapRequestPayload] UpdateUserRequest $request): JsonResponse
    {
        $user = $this->userService->findByIdOrFail($id);
        $user = $this->userService->updateUser($user, $request);

        return $this->json(UserResponse::fromEntity($user));
    }

    #[Route('/{id}', name: 'users_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(
        summary: 'Delete user (Admin only)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: Response::HTTP_NO_CONTENT, description: 'User deleted'),
            new OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized'),
            new OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied'),
            new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'User not found'),
        ],
    )]
    public function delete(int $id): JsonResponse
    {
        $user = $this->userService->findByIdOrFail($id);
        $this->userService->deleteUser($user);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
