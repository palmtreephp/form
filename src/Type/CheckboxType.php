<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;
use Palmtree\NameConverter\SnakeCaseToHumanNameConverter;

class CheckboxType extends AbstractType
{
    protected $type     = 'checkbox';
    protected $value    = '1';
    protected $siblings = false;

    public function getElement(): Element
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

    public function getElements()
    {
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

    public function getNameAttribute(): string
    {
        $formId = $this->form->getKey();

        $format = '%s[%s]';

        if ($this->siblings) {
            $format .= '[]';
        }

        return sprintf($format, $formId, $this->name);
    }

    public function isValid(): bool
    {
        if (!$this->required || !$this->form->isSubmitted()) {
            return true;
        }

        return $this->data && filter_var($this->data, FILTER_VALIDATE_BOOLEAN);
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setSiblings(bool $siblings): self
    {
        $this->siblings = $siblings;

        return $this;
    }

    public function hasSiblings(): bool
    {
        return $this->siblings;
    }

    protected function getIdAttribute(): string
    {
        $attribute = parent::getIdAttribute();

        if ($this->parent) {
            $converter = new SnakeCaseToHumanNameConverter();
            $attribute .= '-' . $converter->denormalize($this->value);
        }

        return $attribute;
    }
}
