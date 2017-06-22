<?php

namespace Palmtree\Form\Constraint;

class NotBlank extends AbstractContstraint implements ConstraintInterface
{
    protected $errorMessage = 'This value must not be blank';

    public function validate($input)
    {
        if (is_array($input)) {
            return !empty($input);
        }

        return strcmp($input, '') !== 0;

    }

}
