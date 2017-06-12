<?php

namespace Palmtree\Form\Type;

class FileType extends AbstractType
{
    protected $type = 'file';

    public function isValid()
    {
        if (!$this->getForm()->isSubmitted() || !$this->isRequired()) {
            return true;
        }

        foreach ($this->getConstraints() as $constraint) {
            if (!$constraint->validate($this->getData())) {
                $this->setErrorMessage($constraint->getErrorMessage());

                return false;
            }
        }

        return true;
    }
}
