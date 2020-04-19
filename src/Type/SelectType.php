<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class SelectType extends AbstractType
{
    protected $tag = 'select';
    /** @var bool */
    protected $multiple = false;

    public function getElement(): Element
    {
        $element = parent::getElement();

        unset($element->attributes['type']);

        if ($this->multiple) {
            $element->attributes->set('multiple');
        } elseif ($placeholder = $element->attributes['placeholder']) {
            unset($element->attributes['placeholder']);

            $option = Element::create('option')->setInnerText($placeholder);

            $option->attributes['value'] = '';

            $element->addChild($option);
        }

        return $element;
    }

    public function getNameAttribute(): string
    {
        $formId = $this->form->getKey();

        $format = '%s[%s]';

        if ($this->multiple) {
            $format .= '[]';
        }

        return sprintf($format, $formId, $this->name);
    }

    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }
}
