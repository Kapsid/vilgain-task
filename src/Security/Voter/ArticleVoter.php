<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Article>
 */
final class ArticleVoter extends Voter
{
    public const EDIT = 'ARTICLE_EDIT';
    public const DELETE = 'ARTICLE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::EDIT, self::DELETE], true)
            && $subject instanceof Article;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Article $article */
        $article = $subject;

        return match ($attribute) {
            self::EDIT => $this->canEdit($article, $user),
            self::DELETE => $this->canDelete($article, $user),
            default => false,
        };
    }

    private function canEdit(Article $article, User $user): bool
    {
        return $user->isAdmin() || $article->getAuthor() === $user;
    }

    private function canDelete(Article $article, User $user): bool
    {
        return $user->isAdmin() || $article->getAuthor() === $user;
    }
}
