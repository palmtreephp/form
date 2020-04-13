<?php

namespace Palmtree\Form\Constraint;

class Number extends AbstractConstraint implements ConstraintInterface
{
    const ERROR_NOT_NUMERIC = 1;
    const ERROR_TOO_SMALL   = 2;
    const ERROR_TOO_LARGE   = 4;

    /** @var int|null */
    private $errorCode;
    /** @var int|null */
    private $min;
    /** @var int|null */
    private $max;

    /**
     * @inheritDoc
     */
    public function validate($input)
    {
        if (!is_numeric($input)) {
            $this->errorCode = self::ERROR_NOT_NUMERIC;

            return false;
        }

        if ($this->getMin() !== null && $input < $this->getMin()) {
            $this->errorCode = self::ERROR_TOO_SMALL;

            return false;
        }

        if ($this->getMax() !== null && $input > $this->getMax()) {
            $this->errorCode = self::ERROR_TOO_LARGE;

            return false;
        }

        return true;
    }

    /**
     * @return int|null
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param int $min
     *
     * @return Number
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param int $max
     *
     * @return Number
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        switch ($this->getErrorCode()) {
            case self::ERROR_TOO_SMALL:
                $errorMessage = 'This value must be greater than or equal to ' . $this->getMin();
                break;
            case self::ERROR_TOO_LARGE:
                $errorMessage = 'This value must be less than ' . $this->getMax();
                break;
            default:
                $errorMessage = 'This value must be a number';
                break;
        }

        return $errorMessage;
    }
}
