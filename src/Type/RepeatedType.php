<?php

namespace Palmtree\Form\Type;

class RepeatedType extends AbstractType
{
    /** @var string */
    private $repeatableType;

    /**
     * @return string
     */
    public function getRepeatableType()
    {
        return $this->repeatableType;
    }

    /**
     * @param string $type
     *
     * @return RepeatedType
     */
    public function setRepeatableType($type)
    {
        $this->repeatableType = $type;

        return $this;
    }
}
