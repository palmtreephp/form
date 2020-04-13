<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;
use Palmtree\NameConverter\SnakeCaseToHumanNameConverter;

class CheckboxType extends AbstractType
{
    protected $type  = 'checkbox';
    protected $value = '1';

    protected $siblings = false;

    public function getElement()
    {
        $element = parent::getElement();

        $element
            ->removeClass('form-control')
            ->addClass('form-check-input')
            ->addAttribute('value', $this->getValue());

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
            $element->addAttribute('checked');
        }

        return $element;
    }

    public function getElements(Element $wrapper = null)
    {
        if ($wrapper instanceof Element) {
            $wrapper->addClass('form-check');
        }

        $formId   = $this->form->getKey();
        $elements = [];

        $element = $this->getElement();

        $name = $this->getName();

        if (!$element->getAttribute('id')) {
            $element->addAttribute('id', "$formId-$name");
        }

        $elements[] = $element;

        $label = $this->getLabelElement();

        if ($label instanceof Element) {
            $label->addClass('form-check-label');

            $elements[] = $label;
        }

        if (!$this->isValid()) {
            $error = $this->getForm()->createInvalidElement();
            $error->setInnerText($this->getErrorMessage());
            $elements[] = $error;
        }

        return $elements;
    }

    public function getNameAttribute()
    {
        $formId = $this->getForm()->getKey();
        $name   = $this->getName();

        if ($this->isGlobal()) {
            return $name;
        }

        $format = '%s[%s]';

        if ($this->hasSiblings()) {
            $format .= '[]';
        }

        return sprintf($format, $formId, $name);
    }

    public function isValid()
    {
        if (!$this->getForm()->isSubmitted() || !$this->isRequired()) {
            return true;
        }

        return $this->getData() && filter_var($this->getData(), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param string $value
     *
     * @return CheckboxType
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param bool $siblings
     *
     * @return CheckboxType
     */
    public function setSiblings($siblings)
    {
        $this->siblings = $siblings;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSiblings()
    {
        return (bool)$this->siblings;
    }

    /**
     * @return mixed|string
     */
    protected function getIdAttribute()
    {
        $attribute = parent::getIdAttribute();

        if ($this->getParent()) {
            $converter = new SnakeCaseToHumanNameConverter();
            $attribute .= '-' . $converter->denormalize($this->getValue());
        }

        return $attribute;
    }
}
