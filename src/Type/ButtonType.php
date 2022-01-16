<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class ButtonType extends AbstractType
{
    protected $tag = 'button';
    protected $type = 'button';
    protected $required = false;
    protected $userInput = false;

    public static $defaultArgs = [
        'placeholder' => false,
        'classes' => [],
    ];

    public function getElement(): Element
    {
        $element = parent::getElement();

        $element->attributes['type'] = $this->type;

        $element->setInnerText($this->label ?? '');

        $element->classes->remove('form-control');

        return $element;
    }

    public function getLabelElement(): ?Element
    {
        return null;
    }
}
