<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint;

use Palmtree\Form\Type\TypeInterface;

class Matching extends AbstractConstraint implements ConstraintInterface
{
    private TypeInterface $matchField;
    protected string $errorMessage = 'Fields do not match';

    public function validate(mixed $input): bool
    {
        return $this->doValidate($input);
    }

    private function doValidate(string $input): bool
    {
        return $input === $this->matchField->getData();
    }

    public function getMatchField(): TypeInterface
    {
        return $this->matchField;
    }

    public function setMatchField(TypeInterface $matchField): self
    {
        $this->matchField = $matchField;

        return $this;
    }
}
