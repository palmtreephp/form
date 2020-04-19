<?php

namespace Palmtree\Form\Constraint;

class Length extends AbstractConstraint implements ConstraintInterface
{
    const ERROR_TOO_SMALL = 1;
    const ERROR_TOO_LARGE = 2;

    /** @var int|null */
    private $min;
    /** @var int|null */
    private $max;
    /** @var int|null */
    private $errorCode;

    public function validate($input)
    {
        if ($this->min !== null && \strlen($input) < $this->min) {
            $this->setErrorCode(self::ERROR_TOO_SMALL);

            return false;
        }

        if ($this->max !== null && \strlen($input) > $this->max) {
            $this->setErrorCode(self::ERROR_TOO_LARGE);

            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
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
            case self::ERROR_TOO_SMALL:
                $errorMessage = "This field must be at least $this->min characters";
                break;
            case self::ERROR_TOO_LARGE:
                $errorMessage = "This field must be less than  $this->max characters";
                break;
            default:
                $errorMessage = 'Invalid string length';
                break;
        }

        return $errorMessage;
    }

    /**
     * @return int|null
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param int|null $min
     *
     * @return Length
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
     * @param int|null $max
     *
     * @return Length
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }
}
