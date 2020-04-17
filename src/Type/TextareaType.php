<?php

namespace Palmtree\Form\Type;

class TextareaType extends AbstractType
{
    protected $tag = 'textarea';

    public function getElement()
    {
        $element = parent::getElement();

        unset($element->attributes['value']);

        if ($data = $this->getData()) {
            $element->setInnerText($data);
        }

        return $element;
    }
}
