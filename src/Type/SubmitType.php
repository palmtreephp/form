<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

class SubmitType extends ButtonType
{
    protected string $type = 'submit';

    public static array $defaultArgs = [
        'placeholder' => false,
        'classes' => [
            'btn',
            'btn-primary',
        ],
    ];
}
