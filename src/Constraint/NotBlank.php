<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint;

class NotBlank extends AbstractConstraint implements ConstraintInterface
{
    protected string $errorMessage = 'Please fill in this field';

    public function validate(mixed $input): bool
    {
        return !($input === false || (empty($input) && $input !== '0' && $input !== 0));
    }
}
