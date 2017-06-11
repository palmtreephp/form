<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class CheckboxType extends AbstractType
{
    protected $type = 'checkbox';

    public function getElement()
    {
        $element = parent::getElement();

        $element
            ->removeClass('form-control')
            ->addClass('form-check-input');

        if (filter_var($this->getData(), FILTER_VALIDATE_BOOLEAN) !== false) {
            $element->addAttribute('checked');
        }

        return $element;
    }

    public function getElements(Element $wrapper = null)
    {
        $wrapper->addClass('form-check');

        $formId   = $this->form->getKey();
        $elements = [];

        $element = $this->getElement();

        $name = $this->getName();

        if (!$element->getAttribute('id')) {
            $element->addAttribute('id', "$formId-$name");
        }

        $label = $this->getLabelElement();

        if ($label instanceof Element) {
            $innerText = $label->getInnerText();

            $label
                ->addChild($element)
                ->removeAttribute('for')
                ->addClass('form-check-label')
                ->setInnerText('');

            $element->setInnerText(' ' . $innerText);

            $elements[] = $label;
        } else {
            $elements[] = $element;
        }

        return $elements;
    }

    public function isValid()
    {
        if (!$this->getForm()->isSubmitted()) {
            return true;
        }

        return $this->getData() && filter_var($this->getData(), FILTER_VALIDATE_BOOLEAN);
    }
}
