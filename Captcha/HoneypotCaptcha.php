<?php

namespace Palmtree\Form\Captcha;

use Palmtree\Html\Element;

class HoneypotCaptcha extends AbstractCaptcha implements CaptchaInterface
{
    public function verify($response)
    {
        return empty($response);
    }

    public function getElements()
    {
        $elements = [];
        $element  = new Element('input.hidden');

        $element->addAttribute('name', $this->getName());

        $elements[] = $element;

        return $elements;
    }
}
