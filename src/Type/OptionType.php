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

        $element = parent::getElement();

        $element->attributes->clear();
        $element->attributes['value'] = $this->getValue();

        $element->classes->clear();

        $element->setInnerText($this->getLabelElement()->getInnerText());

        $data    = $this->getData();
        $compare = true;

        if (\is_array($data)) {
            $key = array_search($this->getValue(), $data);

            if ($key !== false) {
                $data = $data[$key];
            } else {
                $compare = false;
            }
        }

        if ($compare && strcmp($data, $this->getValue()) === 0) {
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
