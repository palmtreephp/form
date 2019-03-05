<?php

namespace Palmtree\Form\Type;

class TextareaType extends AbstractType
{
    protected $tag = 'textarea';

    public function getElement()
    {
        $element = parent::getElement();

        $element->removeAttribute('value');
        $element->setInnerText($this->getData());

        return $element;
    }
}
