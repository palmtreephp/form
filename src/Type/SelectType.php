<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class SelectType extends AbstractType
{
    protected $tag = 'select';
    /** @var bool */
    protected $multiple = false;

    public function getElement()
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

    public function getNameAttribute()
    {
        $formId = $this->form->getKey();

        if ($this->global) {
            return $this->name;
        }

        $format = '%s[%s]';

        if ($this->multiple) {
            $format .= '[]';
        }

        return sprintf($format, $formId, $this->name);
    }

    /**
     * @param bool $multiple
     *
     * @return SelectType
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultiple()
    {
        return $this->multiple;
    }
}
