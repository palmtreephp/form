<?php

namespace Palmtree\Form\Constraint;

class Length extends AbstractConstraint implements ConstraintInterface
{
    /** @var int|null */
    private $min;
    /** @var int|null */
    private $max;
    /** @var int|null */
    private $errorCode;

    const ERROR_TOO_SMALL = 1;
    const ERROR_TOO_LARGE = 2;

    public function validate($input)
    {
        if (!is_null($this->getMin()) && strlen($input) < $this->getMin()) {
            $this->setErrorCode(static::ERROR_TOO_SMALL);

            return false;
        }

        if (!is_null($this->getMax()) && strlen($input) > $this->getMax()) {
            $this->setErrorCode(static::ERROR_TOO_LARGE);

            return false;
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param mixed $errorCode
     *
     * @return Length
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        switch ($this->getErrorCode()) {
            case static::ERROR_TOO_SMALL:
                $errorMessage = 'This field must be at least ' . $this->getMin() . ' characters';
                break;
            case static::ERROR_TOO_LARGE:
                $errorMessage = 'This field must be less than ' . $this->getMax() . ' characters';
                break;
            default:
                $errorMessage = 'Invalid string length';
                break;
        }

        return $errorMessage;
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
     * @return Length
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
     * @return Length
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }
}
