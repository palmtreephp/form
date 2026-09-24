<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

class IntegerType extends AbstractType
{
    use NumberTypeTrait;

    protected string $type = 'number';

    protected function isValidNumber(mixed $value): bool
    {
        return filter_var($value, \FILTER_VALIDATE_INT) !== false;
    }

    protected function getInvalidNumberMessage(): string
    {
        return 'Please enter a whole number';
    }

    public function getNormData(): ?int
    {
        if ($this->data === null || $this->data === '') {
            return null;
        }

        return (int)$this->data;
    }
}
