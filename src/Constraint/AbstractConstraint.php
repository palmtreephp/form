<?php

namespace Palmtree\Form\Constraint;

use Palmtree\ArgParser\ArgParser;

abstract class AbstractConstraint implements ConstraintInterface
{
    /** @var string */
    protected $errorMessage = 'Invalid value';

    /**
     * @param array|string $args
     */
    public function __construct($args = [])
    {
        $parser = new ArgParser($args, 'error_message');
        $parser->parseSetters($this);
    }

    /** {@inheritDoc}
     * @throws \Exception
     */
    public function validate($input): bool
    {
        if (!\is_callable([$this, 'doValidate'])) {
            throw new \Exception();
        }

        return $this->doValidate($input);
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
