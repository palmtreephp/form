<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Form\Constraint\Number;
use Palmtree\Html\Element;

trait NumberTypeTrait
{
    public function isValid(): bool
    {
        if (!parent::isValid()) {
            return false;
        }

        if (!$this->form->isSubmitted() || $this->data === null || $this->data === '') {
            return true;
        }

        if (!$this->isValidNumber($this->data)) {
            $this->setErrorMessage($this->getInvalidNumberMessage());

            return false;
        }

        return true;
    }

    abstract protected function isValidNumber(mixed $value): bool;

    abstract protected function getInvalidNumberMessage(): string;

    public function getElement(): Element
    {
        $element = parent::getElement();

        foreach ($this->constraints as $constraint) {
            if ($constraint instanceof Number) {
                $min = $constraint->getMin();
                $max = $constraint->getMax();

                if ($min !== null) {
                    $element->attributes['min'] = (string)$min;
                }

                if ($max !== null) {
                    $element->attributes['max'] = (string)$max;
                }

                break;
            }
        }

        return $element;
    }
}
