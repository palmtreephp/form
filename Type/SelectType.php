<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class SelectType extends AbstractType
{
    protected $tag = 'select';
    protected $choices = [];

    public function getElement()
    {
        $element = parent::getElement();

        $element->removeAttribute('type');

        $placeholder = $element->getAttribute('placeholder');

        if ($placeholder) {
            $element->removeAttribute('placeholder');

            $option = new Element('option');
            $option->setInnerText($placeholder);

            $element->addChild($option);
        }

        $selected = $this->getData();

        foreach ($this->choices as $value => $text) {
            $option = new Element('option');
            $option->addAttribute('value', $value)->setInnerText($text);

            if (strcmp("$value", "$selected") === 0) {
                $option->addAttribute('selected');
            }

            $element->addChild($option);
        }

        return $element;
    }

    public function setChoices(array $choices)
    {
        $this->choices = $choices;

        return $this;
    }
}
