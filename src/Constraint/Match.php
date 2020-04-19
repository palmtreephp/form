<?php

namespace Palmtree\Form\Constraint;

use Palmtree\Form\Type\AbstractType;

class Match extends AbstractConstraint implements ConstraintInterface
{
    /** @var AbstractType */
    private $matchField;

    protected $errorMessage = 'Fields do not match';

    /**
     * @param $input
     *
     * @return bool
     */
    public function validate($input)
    {
        return $input === $this->matchField->getData();
    }

    /**
     * @return AbstractType
     */
    public function getMatchField()
    {
        return $this->matchField;
    }

    /**
     * @param AbstractType $matchField
     *
     * @return Match
     */
    public function setMatchField($matchField)
    {
        $this->matchField = $matchField;

        return $this;
    }
}
