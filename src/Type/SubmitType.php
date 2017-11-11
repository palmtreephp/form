<?php

namespace Palmtree\Form\Type;

class SubmitType extends ButtonType
{
    protected $type = 'submit';

    public static $defaultArgs = [
        'placeholder' => false,
        'classes'     => [
            'btn',
            'btn-primary',
        ],
    ];
}
