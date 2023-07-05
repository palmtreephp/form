<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

class RepeatedType extends AbstractType
{
    private string $repeatableType;

    public function getRepeatableType(): string
    {
        return $this->repeatableType;
    }

    public function setRepeatableType(string $type): self
    {
        $this->repeatableType = $type;

        return $this;
    }
}
