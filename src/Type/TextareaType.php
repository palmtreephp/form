<?php

namespace Palmtree\Form\Type;

class TextareaType extends AbstractType
{
    protected $tag = 'textarea';

    public function getElement()
    {
        $element = parent::getElement();

        unset($element->attributes['value']);

        if ($this->data) {
            $element->setInnerText($this->data);
        }

        return $element;
    }
}
