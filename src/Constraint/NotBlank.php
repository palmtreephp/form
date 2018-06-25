<?php

namespace Palmtree\Form\Constraint;

class NotBlank extends AbstractConstraint implements ConstraintInterface
{
    protected $errorMessage = 'Please fill in this field';

    /**
     * @inheritDoc
     */
    public function validate($input)
    {
        if ($input === false || (empty($input) && $input != '0')) {
            return false;
        }

        return true;
    }
}
