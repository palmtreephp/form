<?php

namespace Palmtree\Form\Constraint;

class NotBlank extends AbstractConstraint implements ConstraintInterface
{
    protected $errorMessage = 'Please fill in this field';

    public function validate($input): bool
    {
        return !($input === false || (empty($input) && $input !== '0' && $input !== 0));
    }
}
