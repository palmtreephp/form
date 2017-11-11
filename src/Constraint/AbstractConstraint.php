<?php

namespace Palmtree\Form\Constraint;

use Palmtree\ArgParser\ArgParser;

abstract class AbstractConstraint
{
    /** @var string */
    protected $errorMessage = 'Invalid value';

    public function __construct($args = [])
    {
        $parser = new ArgParser($args, 'error_message');
        $parser->parseSetters($this);
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param $message
     *
     * @return AbstractConstraint
     */
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;

        return $this;
    }
}
