<?php

declare(strict_types=1);

namespace Palmtree\Form\Captcha;

class GoogleRecaptcha implements CaptchaInterface, RecaptchaInterface
{
    use RecaptchaTrait;

    public function getVerifyUrl(): string
    {
        return 'https://www.google.com/recaptcha/api/siteverify';
    }

    public function getScriptUrl(): string
    {
        return 'https://www.google.com/recaptcha/api.js';
    }

    public function getType(): string
    {
        return 'grecaptcha';
    }
}
