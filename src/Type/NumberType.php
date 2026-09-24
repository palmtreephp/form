<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

class NumberType extends AbstractType
{
    use NumberTypeTrait;

    protected string $type = 'number';

    protected function isValidNumber(mixed $value): bool
    {
        return is_numeric($value) && is_finite((float)$value);
    }

    protected function getInvalidNumberMessage(): string
    {
        return 'Please enter a number';
    }

    public function getNormData(): ?float
    {
        $data = parent::getData();

        if ($data === null || $data === '') {
            return null;
        }

        return (float)$data;
    }
}
