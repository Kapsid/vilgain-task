<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\RegisterRequest;
use App\DTO\Response\UserResponse;
use App\Service\UserService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/auth')]
#[OA\Tag(name: 'Authentication')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
    ) {
    }

    #[Route('/register', name: 'auth_register', methods: ['POST'])]
    #[OA\Post(
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: RegisterRequest::class)),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: 'User registered successfully',
                content: new OA\JsonContent(ref: new Model(type: UserResponse::class)),
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Validation error',
            ),
            new OA\Response(
                response: Response::HTTP_CONFLICT,
                description: 'Email already exists',
            ),
        ],
    )]
    public function register(#[MapRequestPayload] RegisterRequest $request): JsonResponse
    {
        $user = $this->userService->register($request);

        return $this->json(UserResponse::fromEntity($user), Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'auth_login', methods: ['POST'])]
    #[OA\Post(
        summary: 'Login and get JWT token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string'),
                    ],
                ),
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Invalid credentials',
            ),
        ],
    )]
    public function login(): JsonResponse
    {
        // This method will never be executed because the login is handled by the security firewall
        // This is just for documentation purposes
        return $this->json(['error' => 'This should never be reached'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
