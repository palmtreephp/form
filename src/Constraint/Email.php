<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint;

class Email extends AbstractConstraint implements ConstraintInterface
{
    protected string $errorMessage = 'Please enter a valid email address';

    public function validate(mixed $input): bool
    {
        if ($input !== null && !\is_scalar($input)) {
            return false;
        }

        return $this->doValidate((string)$input);
    }

    protected function doValidate(string $input): bool
    {
        return filter_var($input, \FILTER_VALIDATE_EMAIL) !== false;
    }
}
