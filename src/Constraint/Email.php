<?php

namespace Palmtree\Form\Constraint;

class Email extends AbstractConstraint implements ConstraintInterface
{
    protected $errorMessage = 'Please enter a valid email address';

    protected function doValidate(string $input): bool
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
    }
}
