<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;
use Palmtree\NameConverter\SnakeCaseToHumanNameConverter;

class CheckboxType extends AbstractType
{
    protected string $type = 'checkbox';
    protected string $value = '1';
    protected bool $siblings = false;

    public function getElement(): Element
    {
        $element = parent::getElement();

        $element->classes->remove('form-control');
        $element->classes[] = 'form-check-input';

        $element->attributes['value'] = $this->value;

        $data = $this->data;

        if (\is_array($data)) {
            $key = array_search($this->value, $data);

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
            $attribute .= '-' . (new SnakeCaseToHumanNameConverter())->denormalize($this->value);
        }

        return $attribute;
    }
}
