<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class ButtonType extends AbstractType
{
    protected string $tag = 'button';
    protected string $type = 'button';
    protected bool $required = false;
    protected bool $userInput = false;
    protected bool $mapped = false;

    public static array $defaultArgs = [
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
