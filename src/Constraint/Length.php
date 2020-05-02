<?php

namespace Palmtree\Form\Constraint;

class Length extends AbstractConstraint implements ConstraintInterface
{
    /** @var int */
    public const ERROR_TOO_SMALL = 1;
    /** @var int */
    public const ERROR_TOO_LARGE = 2;

    /** @var int|null */
    private $min;
    /** @var int|null */
    private $max;
    /** @var int|null */
    private $errorCode;

    public function validate($input): bool
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

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function setErrorCode(int $errorCode): self
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    public function getErrorMessage(): string
    {
        switch ($this->errorCode) {
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

    public function getMin(): ?int
    {
        return $this->min;
    }

    public function setMin(?int $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): ?int
    {
        return $this->max;
    }

    public function setMax(?int $max): self
    {
        $this->max = $max;

        return $this;
    }
}
