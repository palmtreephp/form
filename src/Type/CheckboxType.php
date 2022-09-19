<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class CheckboxType extends AbstractType
{
    /** @var string */
    protected $type = 'checkbox';
    /** @var string */
    protected $value = '1';
    /** @var bool */
    protected $siblings = false;

    public function getElement(): Element
    {
        $element = parent::getElement();

        $element->classes->remove('form-control');
        $element->classes[] = 'form-check-input';

        $element->attributes['value'] = $this->value;

        $data = $this->data;

        if (\is_array($data)) {
            $key = array_search($this->value, $data, false);

            if ($key !== false) {
                $data = $data[$key];
            }
        }

        if (\is_scalar($data) && (string)$data === $this->value) {
            $element->attributes->set('checked');
        }

        return $element;
    }

    public function getElements(): array
    {
        $elements = [];

        $element = $this->getElement();

        if (!$element->attributes['id']) {
            $element->attributes['id'] = $this->form->getKey() . "-$this->name";
        }

        $elements[] = $element;

        $label = $this->getLabelElement();

        if ($label instanceof Element) {
            $label->classes[] = 'form-check-label';

            $elements[] = $label;
        }

        if (!$this->isValid() && $this->errorMessage !== null) {
            $elements[] = $this->form->createInvalidElement()->setInnerText($this->errorMessage);
        }

        return $elements;
    }

    public function getNameAttribute(): string
    {
        $value = $this->form->getKey() . "[$this->name]";
        if ($this->siblings) {
            $value .= '[]';
        }

        return $value;
    }

    public function isValid(): bool
    {
        if (!$this->required || !$this->form->isSubmitted()) {
            return true;
        }

        return $this->data && filter_var($this->data, \FILTER_VALIDATE_BOOLEAN);
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
            $attribute .= '-' . $this->nameConverter->denormalize($this->value);
        }

        return $attribute;
    }
}
