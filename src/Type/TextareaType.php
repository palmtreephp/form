<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class TextareaType extends AbstractType
{
    protected $tag = 'textarea';

    public function getElement(): Element
    {
        $element = parent::getElement();

        unset($element->attributes['value']);

        if ($this->data) {
            $element->setInnerText($this->data);
        }

        return $element;
    }
}
