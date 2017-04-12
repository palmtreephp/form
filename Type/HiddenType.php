<?php

namespace Palmtree\Form\Type;

class HiddenType extends AbstractType
{
    protected $type = 'hidden';
    protected $userInput = false;

    public function getLabelElement()
    {
        return false;
    }
}
