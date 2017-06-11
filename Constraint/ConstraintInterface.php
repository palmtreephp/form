<?php

namespace Palmtree\Form\Constraint;

interface ConstraintInterface
{
    /** @return bool */
    public function validate($input);

    /** @return string */
    public function getErrorMessage();
}
