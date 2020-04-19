<?php

namespace Palmtree\Form\Constraint;

interface ConstraintInterface
{
    public function validate($input): bool;

    public function getErrorMessage(): string;
}
