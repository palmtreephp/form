<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint;

class Length extends AbstractConstraint implements ConstraintInterface
{
    final public const ERROR_TOO_SMALL = 1;
    final public const ERROR_TOO_LARGE = 2;

    private ?int $min = null;
    private ?int $max = null;
    private ?int $errorCode = null;

    public function validate(mixed $input): bool
    {
        return $this->doValidate($input);
    }

    private function doValidate(string $input): bool
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
        $errorMessage = match ($this->errorCode) {
            self::ERROR_TOO_SMALL => "This field must be at least $this->min characters",
            self::ERROR_TOO_LARGE => "This field must be less than $this->max characters",
            default => 'Invalid string length',
        };

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
