<?php

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint\AbstractConstraint;
use Palmtree\Form\Constraint\ConstraintInterface;

class Extension extends AbstractConstraint implements ConstraintInterface
{
    protected $extensions = [];

    /**
     * @inheritDoc
     */
    public function validate($uploadedFile)
    {
        $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);

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
        $this->extensions = (array)$extensions;

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
