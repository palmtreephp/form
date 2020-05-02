<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

abstract class AbstractGroupType extends AbstractType
{
    /** @var null */
    protected $errorMessage = null;
    /** @var bool */
    protected $required = false;

    public function getElement(): Element
    {
        $element = new Element('div.' . $this->getGroupName());

        foreach ($this->children as $child) {
            $wrapper = new Element($this->form->getFieldWrapper());
            $wrapper->addChild(...$child->getElements());
            $element->addChild($wrapper);
        }

        return $element;
    }

    public function getGroupName(): string
    {
        $shortClass = substr(strrchr(static::class, '\\'), 1);

        return strtolower(basename($shortClass, 'Type'));
    }
}
