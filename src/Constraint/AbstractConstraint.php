<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint;

use Palmtree\ArgParser\ArgParser;
use Palmtree\Form\UnknownOptions;

abstract class AbstractConstraint implements ConstraintInterface
{
    protected string $errorMessage = 'Invalid value';

    /**
     * @param array<string, mixed>|string $args
     */
    public function __construct(array|string $args = [])
    {
        if (\is_array($args)) {
            UnknownOptions::deprecate($this, $args);
        }

        $parser = new ArgParser($args, 'error_message');
        $parser->parseSetters($this);
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
