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

        unset($element->classes['form-control']);
        $element->classes[] = 'form-check-input';

        $element->attributes['value'] = $this->value;

        $data    = $this->data;
        $compare = true;

        if (\is_array($data)) {
            $key = array_search($this->getValue(), $data, false);

            if ($key !== false) {
                $data = $data[$key];
            } else {
                $compare = false;
            }
        }

        if ($compare && (string)$data === (string)$this->value) {
            $element->attributes->set('checked');
        }

        return $element;
    }

    public function getElements(Element $wrapper = null)
    {
        if ($wrapper instanceof Element) {
            $wrapper->classes[] = 'form-check';
        }

        $formId   = $this->form->getKey();
        $elements = [];

        $element = $this->getElement();

        if (!$element->attributes['id']) {
            $element->attributes['id'] = "$formId-$this->name";
        }

        $elements[] = $element;

        $label = $this->getLabelElement();

        if ($label instanceof Element) {
            $label->classes[] = 'form-check-label';

            $elements[] = $label;
        }

        if (!$this->isValid()) {
            $error = $this->form->createInvalidElement();
            $error->setInnerText($this->getErrorMessage());
            $elements[] = $error;
        }

        return $elements;
    }

    public function getNameAttribute()
    {
        $formId = $this->form->getKey();

        if ($this->global) {
            return $this->name;
        }

        $format = '%s[%s]';

        if ($this->siblings) {
            $format .= '[]';
        }

        return sprintf($format, $formId, $this->name);
    }

    public function isValid()
    {
        if (!$this->required || !$this->form->isSubmitted()) {
            return true;
        }

        return $this->data && filter_var($this->data, FILTER_VALIDATE_BOOLEAN);
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

        if ($this->parent) {
            $converter = new SnakeCaseToHumanNameConverter();
            $attribute .= '-' . $converter->denormalize($this->value);
        }

        return $attribute;
    }
}
