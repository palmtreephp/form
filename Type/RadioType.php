<?php

namespace Palmtree\Form\Type;

class RadioType extends CheckboxType
{
    protected $type = 'radio';

    public function isValid()
    {
        if (!$this->getForm()->isSubmitted()) {
            return true;
        }

        return $this->getData();
    }
}
