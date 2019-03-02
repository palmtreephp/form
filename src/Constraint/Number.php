<?php

namespace Palmtree\Form\Constraint;

class Number extends AbstractConstraint implements ConstraintInterface
{
    const ERROR_NOT_NUMERIC = 1;
    const ERROR_TOO_SMALL   = 2;
    const ERROR_TO_LARGE    = 4;

    /** @var string */
    protected $errorMessage = 'This value must be a number';
    /** @var int */
    protected $errorNumber;
    /** @var int */
    protected $min;
    /** @var int */
    protected $max;

    /**
     * @inheritDoc
     */
    public function validate($input)
    {
        if (!\is_numeric($input)) {
            $this->errorNumber = static::ERROR_NOT_NUMERIC;

            return false;
        }

        $min = $this->getMin();
        $max = $this->getMax();

        if (null !== $min && $input < $min) {
            $this->setErrorMessage(\sprintf('This value must be greater than or equal to %d', $min));
            $this->errorNumber = static::ERROR_TOO_SMALL;

            return false;
        }

        if (null !== $max && $input > $max) {
            $this->setErrorMessage(\sprintf('This value must be less than %d', $max));
            $this->errorNumber = static::ERROR_TO_LARGE;

            return false;
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param mixed $min
     *
     * @return $this
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param mixed $max
     *
     * @return $this
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @return int
     */
    public function getErrorNumber()
    {
        return $this->errorNumber;
    }
}
