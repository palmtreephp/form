<?php

namespace Palmtree\Form\Type;

class RepeatedType extends AbstractType
{
    /** @var string */
    private $repeatableType;

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
