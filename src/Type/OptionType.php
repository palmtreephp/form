<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class OptionType extends AbstractType
{
    protected $tag = 'option';
    protected $value;

    public function getElements(Element $wrapper = null)
    {
        $elements = [];

        $element = $this->getElement();

        $element->attributes->clear();
        $element->attributes['value'] = $this->getValue();

        $element->classes->clear();

        if ($labelElement = $this->getLabelElement()) {
            $element->setInnerText($labelElement->getInnerText());
        }

        $data    = $this->data;
        $compare = true;

        if (\is_array($data)) {
            $key = array_search($this->value, $data, false);

            if ($key !== false) {
                $data = $data[$key];
            } else {
                $compare = false;
            }
        }

        if ($compare && (string)$data === (string)$this->value) {
            $element->attributes->set('selected');
        }

        $elements[] = $element;

        return $elements;
    }

    /**
     * @param mixed $value
     *
     * @return OptionType
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
