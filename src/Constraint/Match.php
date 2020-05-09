<?php

namespace Palmtree\Form\Constraint;

use Palmtree\Form\Type\TypeInterface;

class Match extends AbstractConstraint implements ConstraintInterface
{
    /** @var TypeInterface */
    private $matchField;

    protected $errorMessage = 'Fields do not match';

    public function validate($input): bool
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
