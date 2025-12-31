<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint;

use Palmtree\Form\UnknownOptions;

abstract class AbstractConstraint implements ConstraintInterface
{
    protected string $errorMessage = 'Invalid value';

    /**
     * @param array<string, mixed>|string $args
     */
    public function __construct(array|string $args = [])
    {
        // If string is passed, treat it as error_message
        if (is_string($args)) {
            $this->errorMessage = $args;
            return;
        }

        UnknownOptions::deprecate($this, $args);

        // Process array args
        foreach ($args as $key => $value) {
            // Convert snake_case to camelCase for setter method names
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
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
