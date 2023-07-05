<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class TextareaType extends AbstractType
{
    protected string $tag = 'textarea';

    public function getElement(): Element
    {
        $element = parent::getElement();

        if (isset($element->attributes['value'])) {
            unset($element->attributes['value']);
        }

        if ($this->data) {
            $element->setInnerText($this->data);
        }

        return $element;
    }
}
