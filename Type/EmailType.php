<?php

namespace Palmtree\Form\Type;

class EmailType extends TextType
{
    protected $type = 'email';
    protected $errorMessage = 'Please enter a valid email address';

    public function isValid()
    {
        if (!$this->getForm()->isSubmitted() || $this->required === false) {
            return true;
        }

        return (filter_var($this->getData(), FILTER_VALIDATE_EMAIL) !== false);
    }
}
