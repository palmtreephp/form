<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

abstract class AbstractGroupType extends AbstractType
{
    public function getElement()
    {
        $element = new Element('div.' . $this->getGroupName());

        foreach ($this->getChildren() as $child) {
            $wrapper = new Element($this->getForm()->getFieldWrapper());
            $wrapper->addChildren($child->getElements());
            $element->addChild($wrapper);
        }

        return $element;
    }

    public function getGroupName()
    {
        $shortClass = \substr(\strrchr(static::class, '\\'), 1);
        $name       = \strtolower(\basename($shortClass, 'Type'));

        return $name;
    }
}
