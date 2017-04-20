<?php

namespace Palmtree\Form\Captcha;

use Palmtree\Form\Type\AbstractType;
use Palmtree\Html\Element;

class HoneypotCaptcha extends AbstractCaptcha implements CaptchaInterface
{
    public function __construct(AbstractType $formType)
    {
        parent::__construct($formType);

        $formType->setErrorMessage('The URL field should be left blank');
    }

    public function verify($response)
    {
        return empty($response);
    }

    public function getElements(Element $element)
    {
        $elements = [];

        $element->addAttribute('type', 'text')
                ->addAttribute('style', 'display: none;')
                ->addAttribute('autocomplete', 'off')
                ->removeAttribute('placeholder');

        $elements[] = $element;

        return $elements;
    }
}
