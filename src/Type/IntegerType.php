<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

class IntegerType extends NumberType
{
    public function getNormData()
    {
        if ($this->data === null) {
            return null;
        }

        return (int)$this->data;
    }
}
