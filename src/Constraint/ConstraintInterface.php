<?php

namespace Palmtree\Form\Constraint;

interface ConstraintInterface
{
    /**
     * @param mixed $input
     *
     * @return bool
     */
    public function validate($input);

    /** @return string */
    public function getErrorMessage();
}
