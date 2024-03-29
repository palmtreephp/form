<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint;

class NotBlank extends AbstractConstraint implements ConstraintInterface
{
    /** @var string */
    protected $errorMessage = 'Please fill in this field';

    public function validate($input): bool
    {
        return !($input === false || (empty($input) && $input !== '0' && $input !== 0));
    }
}
