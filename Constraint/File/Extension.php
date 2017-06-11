<?php

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint\AbstractContstraint;
use Palmtree\Form\Constraint\ConstraintInterface;

class Extension extends AbstractContstraint implements ConstraintInterface
{
    protected $extensions = [];

    /**
     * @inheritDoc
     */
    public function validate($file)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (!in_array($extension, $this->getExtensions())) {
            $this->setErrorMessage(
                sprintf('File must have one of the following extensions: %s', implode(',', $this->getExtensions()))
            );

            return false;
        }

        return true;
    }

    /**
     * @param mixed $extensions
     *
     * @return Extension
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
}
