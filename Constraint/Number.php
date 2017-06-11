<?php

namespace Palmtree\Form\Constraint;

class Number extends AbstractContstraint implements ConstraintInterface
{
    protected $errorMessage = 'This value must be a number';

    protected $min;
    protected $max;

    /**
     * @inheritDoc
     */
    public function validate($input)
    {
        if (!is_numeric($input)) {
            return false;
        }

        $min = $this->getMin();
        $max = $this->getMax();

        if (!is_null($min) && $input < $min) {
            $this->setErrorMessage(sprintf('This value must be greater than %d', $min));

            return false;
        }

        if (!is_null($max) && $input > $max) {
            $this->setErrorMessage(sprintf('This value must be less than %d', $min));

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
}
