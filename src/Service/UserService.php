<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Request\CreateUserRequest;
use App\DTO\Request\RegisterRequest;
use App\DTO\Request\UpdateUserRequest;
use App\Entity\User;
use App\Exception\EmailAlreadyExistsException;
use App\Exception\EntityNotFoundException;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function register(RegisterRequest $request): User
    {
        $this->ensureEmailNotExists($request->email);

        $user = new User();
        $user->setEmail($request->email);
        $user->setName($request->name);
        $user->setRole($request->getUserRole());
        $user->setPassword($this->passwordHasher->hashPassword($user, $request->password));

        $this->userRepository->save($user, true);

        return $user;
    }

    public function createUser(CreateUserRequest $request): User
    {
        $this->ensureEmailNotExists($request->email);

        $user = new User();
        $user->setEmail($request->email);
        $user->setName($request->name);
        $user->setRole($request->getUserRole());
        $user->setPassword($this->passwordHasher->hashPassword($user, $request->password));

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
            $user->setRole($request->getUserRole());
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

    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
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
            throw new EmailAlreadyExistsException($email);
        }
    }
}
