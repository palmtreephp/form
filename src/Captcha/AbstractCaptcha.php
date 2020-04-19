<?php

namespace Palmtree\Form\Captcha;

abstract class AbstractCaptcha
{
    public function getName(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function getSlug(): string
    {
        return strtolower(str_replace(' ', '_', $this->getName()));
    }

    public function getErrorMessage(): string
    {
        return 'Captcha failed';
    }
}
