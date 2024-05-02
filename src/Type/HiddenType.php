<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class HiddenType extends AbstractType
{
    protected string $type = 'hidden';
    protected bool $userInput = false;

    public function getLabelElement(): ?Element
    {
        return null;
    }
}
