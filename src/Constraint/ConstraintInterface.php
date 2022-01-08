<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint;

interface ConstraintInterface
{
    /** @param mixed $input */
    public function validate($input): bool;

    public function getErrorMessage(): string;
}
