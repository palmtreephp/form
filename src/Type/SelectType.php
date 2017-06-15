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

        $element->removeAttribute('type');

        if ($this->isMultiple()) {
            $element->addAttribute('multiple');
        }

        $placeholder = $element->getAttribute('placeholder');

        if ($placeholder) {
            $element->removeAttribute('placeholder');

            $option = new Element('option');
            $option
                ->setInnerText($placeholder)
                ->addAttribute('value', '');

            $element->addChild($option);
        }

        return $element;
    }

    public function getNameAttribute()
    {
        $formId = $this->getForm()->getKey();
        $name   = $this->getName();

        if ($this->isGlobal()) {
            return $name;
        }

        $format = '%s[%s]';

        if ($this->isMultiple()) {
            $format .= '[]';
        }

        return sprintf($format, $formId, $name);
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
