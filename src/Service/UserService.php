<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Request\CreateUserRequest;
use App\DTO\Request\RegisterRequest;
use App\DTO\Request\UpdateUserRequest;
use App\Entity\User;
use App\Enum\UserRole;
use App\Exception\EmailAlreadyExistsException;
use App\Exception\EntityNotFoundException;
use App\Factory\UserFactory;
use App\Repository\UserRepository;

final readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserFactory $userFactory,
    ) {
    }

    public function register(RegisterRequest $request): User
    {
        return $this->createUserInternal(
            $request->email,
            $request->password,
            $request->name,
            $request->getUserRole(),
        );
    }

    public function createUser(CreateUserRequest $request): User
    {
        return $this->createUserInternal(
            $request->email,
            $request->password,
            $request->name,
            $request->getUserRole(),
        );
    }

    private function createUserInternal(
        string $email,
        string $password,
        string $name,
        UserRole $role,
    ): User {
        $this->ensureEmailNotExists($email);

        $user = $this->userFactory->create($email, $password, $name, $role);

        $this->userRepository->save($user, true);

        return $user;
    }

    public function updateUser(User $user, UpdateUserRequest $request): User
    {
        if (null !== $request->email && $request->email !== $user->getEmail()) {
            $this->ensureEmailNotExists($request->email);
            $user->setEmail($request->email);
        }

        if (null !== $request->name) {
            $user->setName($request->name);
        }

        if (null !== $request->role) {
            $user->setRole(UserRole::from($request->role));
        }

        $this->userRepository->save($user, true);

        return $user;
    }

    public function deleteUser(User $user): void
    {
        $this->userRepository->remove($user, true);
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function findByIdOrFail(int $id): User
    {
        return $this->userRepository->find($id)
            ?? throw new EntityNotFoundException('User', $id);
    }

    /**
     * @return User[]
     */
    public function findAll(int $limit = 50, int $offset = 0): array
    {
        return $this->userRepository->findBy([], ['id' => 'ASC'], $limit, $offset);
    }

    public function countAll(): int
    {
        return $this->userRepository->count([]);
    }

    public function emailExists(string $email): bool
    {
        return null !== $this->userRepository->findByEmail($email);
    }

    private function ensureEmailNotExists(string $email): void
    {
        if ($this->emailExists($email)) {
            throw new EmailAlreadyExistsException();
        }
    }
}
