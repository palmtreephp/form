<?php

namespace Palmtree\Form\Captcha;

use Palmtree\Form\Type\AbstractType;

abstract class AbstractCaptcha
{
    /** @var AbstractType */
    protected $formType;

    public function __construct(AbstractType $formType)
    {
        $this->formType = $formType;
    }

    public function getName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function getSlug()
    {
        return strtolower(str_replace(' ', '_', $this->getName()));
    }
}
