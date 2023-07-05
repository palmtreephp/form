<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

class IntegerType extends AbstractType
{
    use NumberTypeTrait;

    public function getNormData(): ?int
    {
        if ($this->data === null) {
            return null;
        }

        return (int)$this->data;
    }
}
