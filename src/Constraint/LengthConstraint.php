<?php

namespace Palmtree\Form\Constraint;

class LengthConstraint extends AbstractConstraint implements ConstraintInterface
{
    /** @var int|null */
    private $minLength;
    /** @var int|null */
    private $maxLength;
    /** @var int|null */
    private $errorCode;

    const ERROR_TOO_SMALL = 1;
    const ERROR_TOO_LARGE = 2;

    public function validate($input)
    {
        if (!is_null($this->getMinLength()) && strlen($input) < $this->getMinLength()) {
            $this->setErrorCode(static::ERROR_TOO_SMALL);

            return false;
        }

        if (!is_null($this->getMaxLength()) && strlen($input) > $this->getMaxLength()) {
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
     * @return LengthConstraint
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    public function getErrorMessage()
    {
        switch ($this->getErrorCode()) {
            case static::ERROR_TOO_SMALL:
                $errorMessage = 'This field must be at least ' . $this->getMinLength() . ' characters';
                break;
            case static::ERROR_TOO_LARGE:
                $errorMessage = 'This field must be less than ' . $this->getMaxLength() . ' characters';
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
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * @param mixed $minLength
     *
     * @return LengthConstraint
     */
    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * @param mixed $maxLength
     *
     * @return LengthConstraint
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;

        return $this;
    }
}
