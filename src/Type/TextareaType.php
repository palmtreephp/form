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

        unset($element->attributes['type'], $element->attributes['value']);

        if (\is_scalar($this->data)) {
            $element->setInnerText((string)$this->data);
        }

        return $element;
    }
}
