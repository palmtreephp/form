<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint;

use Palmtree\Form\OptionSetter;
use Palmtree\Form\UnknownOptions;

abstract class AbstractConstraint implements ConstraintInterface
{
    protected string $errorMessage = 'Invalid value';

    /**
     * @param array<string, mixed>|string $args
     */
    public function __construct(array|string $args = [])
    {
        if (\is_string($args)) {
            $this->errorMessage = $args;

            return;
        }

        UnknownOptions::deprecate($this, $args);
        OptionSetter::apply($this, $args);
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(string $message): self
    {
        $this->errorMessage = $message;

        return $this;
    }
}
