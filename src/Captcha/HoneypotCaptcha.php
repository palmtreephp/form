<?php

declare(strict_types=1);

namespace Palmtree\Form\Captcha;

use Palmtree\Form\Form;
use Palmtree\Html\Element;

class HoneypotCaptcha implements CaptchaInterface
{
    public function verify($input): bool
    {
        return $this->doVerify($input);
    }

    protected function doVerify(string $input): bool
    {
        return $input === '';
    }

    public function getErrorMessage(): string
    {
        return 'This is a honeypot field and should be left blank.';
    }

    /** {@inheritDoc} */
    public function getElements(Element $element, Form $form): array
    {
        $elements = [];

        unset($element->attributes['placeholder']);

        $element->attributes->add([
            'type' => 'text',
            'style' => 'display: none;',
            'autocomplete' => 'off',
        ]);

        $elements[] = $element;

        return $elements;
    }
}
