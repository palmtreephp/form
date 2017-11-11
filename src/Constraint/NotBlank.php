<?php

namespace Palmtree\Form\Constraint;

class NotBlank extends AbstractConstraint implements ConstraintInterface
{
    protected $errorMessage = 'This value must not be blank';

    /**
     * @inheritDoc
     */
    public function validate($input)
    {
        if (is_array($input)) {
            return !empty($input);
        }

        return strcmp($input, '') !== 0;
    }
}
