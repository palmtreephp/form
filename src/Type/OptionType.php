<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

class OptionType extends AbstractType
{
    /** @var string */
    protected $tag = 'option';
    /** @var string */
    protected $value;

    public function getElements(): array
    {
        $elements = [];

        $element = $this->getElement();

        $element->attributes->clear();
        $element->attributes['value'] = $this->value;

        $element->classes->clear();

        if ($labelElement = $this->getLabelElement()) {
            $element->setInnerText($labelElement->getInnerText());
        }

        $data = $this->data;

        if (\is_array($data)) {
            $key = array_search($this->value, $data, false);

            if ($key !== false) {
                $data = $data[$key];
            }
        }

        if (\is_scalar($data) && (string)$data === $this->value) {
            $element->attributes->set('selected');
        }

        $elements[] = $element;

        return $elements;
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

    public function getPlaceHolderAttribute(): string
    {
        return '';
    }
}
