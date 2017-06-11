<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class RadioType extends AbstractType
{
    protected $type = 'radio';
    protected $choices = [];

    public function getElement()
    {
        $parent = parent::getElement();

        $wrapper = new Element('div');

        $selected = $this->getData();

        $cloneeOption = new Element('input');

        $cloneeOption->setAttributes($parent->getAttributes());

        $cloneeOption->addClass('form-check-input')
                      ->addClass('palmtree-form-control')
                      ->addAttribute('type', 'radio');

        $i = 0;

        foreach ($this->choices as $value => $text) {
            $i++;

            $element = new Element('div.form-check');

            $label = new Element('label.form-check-label');

            $option = clone $cloneeOption;

            $option
                ->addAttribute('value', $value)->setInnerText(' ' . $text)
                ->addAttribute('name', $this->getNameAttribute())
                ->addAttribute('id', sprintf('%s_%d', $option->getAttribute('id'), $i));

            if (strcmp("$value", "$selected") === 0) {
                $option->addAttribute('checked');
            }

            $label->addChild($option);

            $element->addChild($label);

            $wrapper->addChild($element);
        }

        return $wrapper;
    }

    public function getElements(Element $wrapper = null)
    {
        $elements = [$this->getElement()];

        return $elements;
    }

    public function isValid()
    {
        if (!$this->getForm()->isSubmitted()) {
            return true;
        }

        return $this->getData() && filter_var($this->getData(), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param array $choices
     *
     * @return RadioType
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;

        return $this;
    }
}
