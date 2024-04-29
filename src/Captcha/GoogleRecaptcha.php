<?php

declare(strict_types=1);

namespace Palmtree\Form\Captcha;

use Palmtree\Form\Exception\OutOfBoundsException;
use Palmtree\Form\Form;
use Palmtree\Html\Element;

class GoogleRecaptcha implements CaptchaInterface
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    private const SCRIPT_URL = 'https://www.google.com/recaptcha/api.js';

    private const ERROR_CODES = [
        'missing-input-secret' => 'The secret parameter is missing.',
        'invalid-input-secret' => 'The secret parameter is invalid or malformed.',
        'missing-input-response' => 'The response parameter is missing.',
        'invalid-input-response' => 'The response parameter is invalid or malformed.',
    ];
    private readonly string $ip;
    /** @var list<string> */
    private array $errors = [];
    /** @var array<string, array{success: bool, error-codes: list<key-of<self::ERROR_CODES>>}> */
    private array $verificationResult = [];
    private bool $autoload = true;

    /**
     * @param string $siteKey   Site key obtained from Google Recaptcha admin
     * @param string $secretKey Secret key obtained from Google Recaptcha admin
     */
    public function __construct(private readonly string $siteKey, private readonly string $secretKey, ?string $ip = null)
    {
        if (!\extension_loaded('curl')) {
            throw new \RuntimeException('The curl extension is required to use Google Recaptcha');
        }

        $this->ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public function verify(mixed $input): bool
    {
        return $this->doVerify($input);
    }

    /**
     * @param string $input The form's 'g-recaptcha-response' field value.
     */
    protected function doVerify(string $input): bool
    {
        $result = $this->getVerificationResult($input);

        if ($result === null) {
            return false;
        }

        if ($result['success']) {
            return true;
        }

        if (!empty($result['error-codes'])) {
            $this->errors = $result['error-codes'];
        }

        return false;
    }

    public function getElements(Element $element, Form $form): array
    {
        if (!$element->attributes['id']) {
            $element->attributes['id'] = 'g-recaptcha-' . uniqid();
        }

        $controlId = (string)$element->attributes['id'];

        $element->classes->remove('palmtree-form-control');

        $element->attributes->set('hidden');

        // Placeholder Element that actually displays the captcha
        $placeholderId = sprintf('%s_placeholder', $controlId);

        $placeholder = new Element('div.palmtree-form-control.g-recaptcha');

        $placeholder->attributes['id'] = $placeholderId;

        if (!$element->attributes['data-name']) {
            throw new OutOfBoundsException('Required data-name attribute missing from recaptcha element');
        }

        $placeholder->attributes->setData('name', $element->attributes['data-name']);

        unset($element->attributes['data-name']);

        $placeholder->attributes->setData('site_key', $this->siteKey);
        $placeholder->attributes->setData('form_control', $controlId);

        $onloadCallback = sprintf('%s_onload', str_replace('-', '_', $controlId));
        $placeholder->attributes->setData('script_url', $this->getScriptSrc($onloadCallback));
        $placeholder->attributes->setData('onload', $onloadCallback);

        if ($this->autoload) {
            $placeholder->classes[] = 'g-recaptcha-autoload';
        }

        return [
            $placeholder,
            $element,
        ];
    }

    public function getErrorMessage(): string
    {
        if (empty($this->errors)) {
            return '';
        }

        $error = reset($this->errors);

        return self::ERROR_CODES[$error] ?? $error;
    }

    /**
     * Returns the recaptcha API script source with an onload callback.
     */
    private function getScriptSrc(string $onloadCallbackName): string
    {
        $url = self::SCRIPT_URL;

        parse_str((string)parse_url($url, \PHP_URL_QUERY), $queryArgs);

        $queryArgs['onload'] = $onloadCallbackName;
        $queryArgs['render'] = 'explicit';

        return sprintf('%s?%s', strtok($url, '?'), http_build_query($queryArgs));
    }

    /**
     * @return array{success: bool, error-codes: list<key-of<self::ERROR_CODES>>}|null
     *
     * @throws \JsonException
     */
    private function getVerificationResult(string $response): ?array
    {
        if (!isset($this->verificationResult[$response])) {
            $postFields = [
                'secret' => $this->secretKey,
                'response' => $response,
            ];

            if ($this->ip) {
                $postFields['remoteip'] = $this->ip;
            }

            $handle = curl_init(self::VERIFY_URL);

            if (!$handle instanceof \CurlHandle) {
                return null;
            }

            curl_setopt($handle, \CURLOPT_POST, \count($postFields));
            curl_setopt($handle, \CURLOPT_POSTFIELDS, http_build_query($postFields));
            curl_setopt($handle, \CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($handle);

            if (!$result || !\is_string($result)) {
                return null;
            }

            $this->verificationResult[$response] = json_decode($result, true, 512, \JSON_THROW_ON_ERROR);
        }

        return $this->verificationResult[$response];
    }

    public function setAutoload(bool $autoload): self
    {
        $this->autoload = $autoload;

        return $this;
    }

    public function isAutoload(): bool
    {
        return $this->autoload;
    }
}
