<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint;

interface ConstraintInterface
{
    public function validate(mixed $input): bool;

    public function getErrorMessage(): string;
}
