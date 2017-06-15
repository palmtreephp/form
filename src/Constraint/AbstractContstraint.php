<?php

namespace Palmtree\Form\Constraint;

use Palmtree\ArgParser\ArgParser;

abstract class AbstractContstraint
{
    protected $errorMessage = 'Invalid value';

    public function __construct($args = [])
    {
        $parser = new ArgParser($args);
        $parser->parseSetters($this);
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
    }
}
