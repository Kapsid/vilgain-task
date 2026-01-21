<?php

declare(strict_types=1);

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

/**
 * Compound constraint for strong password validation.
 * Combines length and complexity requirements.
 *
 * Suggestion: For production, consider adding Assert\NotCompromisedPassword
 * to check passwords against the Have I Been Pwned database.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class StrongPassword extends Compound
{
    /**
     * @param array<string, mixed> $options
     *
     * @return list<Constraint>
     */
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(),
            new Assert\Length(
                min: 12,
                max: 255,
                minMessage: 'Password must be at least 12 characters long.',
            ),
            new Assert\Regex(
                pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\\\|,.<>\/?]).+$/',
                message: 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            ),
        ];
    }
}
