<?php

declare(strict_types=1);

namespace Palmtree\Form\Captcha;

class HCaptcha implements CaptchaInterface, RecaptchaInterface
{
    use RecaptchaTrait;

    public function getVerifyUrl(): string
    {
        return 'https://hcaptcha.com/siteverify';
    }

    public function getScriptUrl(): string
    {
        return 'https://js.hcaptcha.com/1/api.js';
    }

    public function getType(): string
    {
        return 'hcaptcha';
    }
}
