<?php

declare(strict_types=1);

namespace Palmtree\Form\Captcha;

use Palmtree\Form\Form;
use Palmtree\Html\Element;

class HoneypotCaptcha implements CaptchaInterface
{
    public function verify(mixed $input): bool
    {
        return \is_string($input) && $this->doVerify($input);
    }

    protected function doVerify(string $input): bool
    {
        return $input === '';
    }

    public function getErrorMessage(): string
    {
        return 'Your submission could not be processed. Please try again.';
    }

    public function getElements(Element $element, Form $form): array
    {
        $elements = [];

        // The field must be submitted empty, so it can't be required, and it is hidden from users,
        // keyboard navigation and assistive technology
        unset($element->attributes['placeholder'], $element->attributes['required']);

        $element->attributes->add([
            'type' => 'text',
            'style' => 'display: none;',
            'autocomplete' => 'off',
            'tabindex' => '-1',
            'aria-hidden' => 'true',
        ]);

        $elements[] = $element;

        return $elements;
    }
}
