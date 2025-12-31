<?php

declare(strict_types=1);

namespace Palmtree\Form\Csrf;

interface CsrfValidatorInterface
{
    public function validate(): void;
}
