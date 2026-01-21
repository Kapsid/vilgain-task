<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const BRITISH_FIRST_NAMES = [
        'Oliver', 'George', 'Harry', 'Jack', 'Charlie', 'Thomas', 'James', 'William',
        'Oscar', 'Henry', 'Alexander', 'Edward', 'Samuel', 'Daniel', 'Arthur',
        'Joseph', 'David', 'Benjamin', 'Frederick', 'Richard', 'Matthew', 'Robert',
        'Amelia', 'Olivia', 'Emily', 'Isla', 'Ava', 'Sophie', 'Mia', 'Charlotte',
        'Grace', 'Lily', 'Evie', 'Ella', 'Scarlett', 'Poppy', 'Isabella', 'Freya',
        'Florence', 'Alice', 'Victoria', 'Elizabeth', 'Eleanor', 'Catherine', 'Margaret',
    ];

    private const BRITISH_SURNAMES = [
        'Smith', 'Jones', 'Williams', 'Brown', 'Taylor', 'Davies', 'Wilson', 'Evans',
        'Thomas', 'Johnson', 'Roberts', 'Walker', 'Wright', 'Robinson', 'Thompson',
        'White', 'Hughes', 'Edwards', 'Green', 'Hall', 'Lewis', 'Harris', 'Clarke',
        'Patel', 'Jackson', 'Wood', 'Turner', 'Martin', 'Cooper', 'Hill', 'Ward',
        'Morris', 'Moore', 'Clark', 'Lee', 'King', 'Baker', 'Harrison', 'Morgan',
        'Allen', 'James', 'Scott', 'Phillips', 'Watson', 'Davis', 'Parker', 'Price',
    ];

    private const ROLE_ADMIN = 0;
    private const ROLE_AUTHOR = 1;
    private const ROLE_READER = 2;

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setName('Admin User');
        $admin->setRole(UserRole::ADMIN);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'AdminPass123'));
        $manager->persist($admin);

        $author = new User();
        $author->setEmail('author@example.com');
        $author->setName('Author User');
        $author->setRole(UserRole::AUTHOR);
        $author->setPassword($this->passwordHasher->hashPassword($author, 'AuthorPass123'));
        $manager->persist($author);

        $reader = new User();
        $reader->setEmail('reader@example.com');
        $reader->setName('Reader User');
        $reader->setRole(UserRole::READER);
        $reader->setPassword($this->passwordHasher->hashPassword($reader, 'ReaderPass123'));
        $manager->persist($reader);

        // Random users
        $usedEmails = [];
        $roles = [UserRole::ADMIN, UserRole::AUTHOR, UserRole::READER];

        for ($i = 0; $i < 50; ++$i) {
            $firstName = self::BRITISH_FIRST_NAMES[array_rand(self::BRITISH_FIRST_NAMES)];
            $surname = self::BRITISH_SURNAMES[array_rand(self::BRITISH_SURNAMES)];
            $name = $firstName.' '.$surname;

            $baseEmail = strtolower($firstName.'.'.$surname);
            $email = $baseEmail.'@example.com';
            $counter = 1;
            while (\in_array($email, $usedEmails, true)) {
                $email = $baseEmail.$counter.'@example.com';
                ++$counter;
            }
            $usedEmails[] = $email;

            // Assign roles
            $roleIndex = $this->weightedRoleIndex();
            $role = $roles[$roleIndex];

            $user = new User();
            $user->setEmail($email);
            $user->setName($name);
            $user->setRole($role);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'SecurePass123'));
            $manager->persist($user);
        }

        // Create Articles
        $article1 = new Article();
        $article1->setTitle('Test article');
        $article1->setContent('This is the content of the first article created by admin.');
        $article1->setAuthor($admin);
        $manager->persist($article1);

        $article2 = new Article();
        $article2->setTitle('Test article - volume two');
        $article2->setContent('A comprehensive guide about writing articles in the system.');
        $article2->setAuthor($author);
        $manager->persist($article2);

        $article3 = new Article();
        $article3->setTitle('Test article - volume three');
        $article3->setContent('Welcome to our platform! This article will help you get started.');
        $article3->setAuthor($author);
        $manager->persist($article3);

        $manager->flush();
    }

    private function weightedRoleIndex(): int
    {
        $rand = random_int(1, 100);
        // 5% admins
        if ($rand <= 5) {
            return self::ROLE_ADMIN;
        }
        // 20% authors
        if ($rand <= 25) {
            return self::ROLE_AUTHOR;
        }

        // 75% readers
        return self::ROLE_READER;
    }
}
