<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

class NumberType extends AbstractType
{
    use NumberTypeTrait;

    protected string $type = 'number';

    public function getNormData(): ?float
    {
        $data = parent::getData();

        if ($data === null) {
            return null;
        }

        return (float)$data;
    }
}
