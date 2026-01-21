<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Validator\StrongPassword;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

final class StrongPasswordTest extends TestCase
{
    #[Test]
    public function containsAllRequiredConstraints(): void
    {
        $constraint = new StrongPassword();
        $constraints = $constraint->constraints;

        $constraintTypes = array_map(fn ($c) => $c::class, $constraints);

        $this->assertContains(NotBlank::class, $constraintTypes);
        $this->assertContains(Length::class, $constraintTypes);
        $this->assertContains(Regex::class, $constraintTypes);
        $this->assertCount(3, $constraints);
    }

    #[Test]
    public function lengthConstraintRequiresMinimum12Characters(): void
    {
        $constraint = new StrongPassword();

        $lengthConstraint = null;
        foreach ($constraint->constraints as $c) {
            if ($c instanceof Length) {
                $lengthConstraint = $c;
                break;
            }
        }

        $this->assertNotNull($lengthConstraint);
        $this->assertSame(12, $lengthConstraint->min);
        $this->assertSame(255, $lengthConstraint->max);
    }

    #[Test]
    public function regexConstraintRequiresComplexity(): void
    {
        $constraint = new StrongPassword();

        $regexConstraint = null;
        foreach ($constraint->constraints as $c) {
            if ($c instanceof Regex) {
                $regexConstraint = $c;
                break;
            }
        }

        $this->assertNotNull($regexConstraint);
        $this->assertNotNull($regexConstraint->pattern);

        // Pattern should require lowercase, uppercase, digit, and special character
        $pattern = $regexConstraint->pattern;
        $this->assertMatchesRegularExpression($pattern, 'ValidPass123!');
        $this->assertDoesNotMatchRegularExpression($pattern, 'alllowercase!');
        $this->assertDoesNotMatchRegularExpression($pattern, 'ALLUPPERCASE!');
        $this->assertDoesNotMatchRegularExpression($pattern, '12345678901!');
        $this->assertDoesNotMatchRegularExpression($pattern, 'ValidPass123'); // missing special char
    }
}
