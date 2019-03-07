<?php

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint\AbstractConstraint;
use Palmtree\Form\Constraint\ConstraintInterface;

class Extension extends AbstractConstraint implements ConstraintInterface
{
    private $extensions = [];

    /**
     * @inheritDoc
     */
    public function validate($uploadedFile)
    {
        $extension = \pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);

        if (!\in_array($extension, $this->getExtensions())) {
            $this->setErrorMessage(
                'Only the following file extensions are allowed: ' . \implode(', ', $this->getExtensions())
            );

            return false;
        }

        return true;
    }

    /**
     * @param array $extensions
     *
     * @return Extension
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;

        return $this;
    }

    /**
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
}
