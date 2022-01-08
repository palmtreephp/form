<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint;

class Number extends AbstractConstraint implements ConstraintInterface
{
    public const ERROR_NOT_NUMERIC = 1;
    public const ERROR_TOO_SMALL = 2;
    public const ERROR_TOO_LARGE = 4;

    /** @var int|null */
    private $errorCode;
    /** @var int|null */
    private $min;
    /** @var int|null */
    private $max;

    public function validate($input): bool
    {
        if (!is_numeric($input)) {
            $this->errorCode = self::ERROR_NOT_NUMERIC;

            return false;
        }

        if ($this->min !== null && $input < $this->min) {
            $this->errorCode = self::ERROR_TOO_SMALL;

            return false;
        }

        if ($this->max !== null && $input > $this->max) {
            $this->errorCode = self::ERROR_TOO_LARGE;

            return false;
        }

        return true;
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

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): string
    {
        switch ($this->errorCode) {
            case self::ERROR_TOO_SMALL:
                $errorMessage = "This value must be greater than or equal to $this->min";
                break;
            case self::ERROR_TOO_LARGE:
                $errorMessage = "'This value must be less than $this->max";
                break;
            default:
                $errorMessage = 'This value must be a number';
                break;
        }

        return $errorMessage;
    }
}
