<?php

declare(strict_types=1);

namespace Palmtree\Form\Captcha;

interface RecaptchaInterface
{
    public const ERROR_CODES = [
        'missing-input-secret' => 'The secret parameter is missing.',
        'invalid-input-secret' => 'The secret parameter is invalid or malformed.',
        'missing-input-response' => 'The response parameter is missing.',
        'invalid-input-response' => 'The response parameter is invalid or malformed.',
    ];

    public function getScriptUrl(): string;

    public function getVerifyUrl(): string;

    public function getType(): string;
}
