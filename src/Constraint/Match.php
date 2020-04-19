<?php

namespace Palmtree\Form\Constraint;

use Palmtree\Form\Type\AbstractType;

class Match extends AbstractConstraint implements ConstraintInterface
{
    /** @var AbstractType */
    private $matchField;

    protected $errorMessage = 'Fields do not match';

    public function validate($input): bool
    {
        return $input === $this->matchField->getData();
    }

    public function getMatchField(): AbstractType
    {
        return $this->matchField;
    }

    public function setMatchField(AbstractType $matchField): self
    {
        $this->matchField = $matchField;

        return $this;
    }
}
