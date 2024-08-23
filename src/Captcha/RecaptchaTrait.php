<?php

declare(strict_types=1);

namespace Palmtree\Form\Captcha;

use Palmtree\Form\Exception\OutOfBoundsException;
use Palmtree\Form\Form;
use Palmtree\Html\Element;

trait RecaptchaTrait
{
    private readonly string $ip;
    /** @var list<string> */
    private array $errors = [];
    /** @var array<string, array{success: bool, error-codes: list<key-of<self::ERROR_CODES>>}> */
    private array $verificationResult = [];
    private bool $autoload = true;

    public function __construct(private readonly string $siteKey, private readonly string $secretKey, ?string $ip = null)
    {
        if (!\extension_loaded('curl')) {
            throw new \RuntimeException('The curl extension is required for captcha support');
        }

        $this->ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public function setAutoload(bool $autoload): void
    {
        $this->autoload = $autoload;
    }

    public function isAutoload(): bool
    {
        return $this->autoload;
    }

    public function verify(mixed $input): bool
    {
        return $this->doVerify($input);
    }

    /**
     * @param string $input The form's captcha response field value.
     */
    private function doVerify(string $input): bool
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
            $element->attributes['id'] = \sprintf('palmtree-captcha-%s', uniqid());
        }

        $controlId = (string)$element->attributes['id'];

        $element->classes->remove('palmtree-form-control');

        $element->attributes->set('hidden');

        // Placeholder Element that actually displays the captcha
        $placeholderId = \sprintf('%s_placeholder', $controlId);

        $placeholder = new Element('div.palmtree-form-control');

        $placeholder->attributes['id'] = $placeholderId;

        if (!$element->attributes['data-name']) {
            throw new OutOfBoundsException('Required data-name attribute missing from recaptcha element');
        }

        $placeholder->attributes->setData('name', $element->attributes['data-name']);

        unset($element->attributes['data-name']);

        $onloadCallback = \sprintf('%s_onload', str_replace('-', '_', $controlId));

        $config = htmlentities(json_encode([
            'type' => $this->getType(),
            'siteKey' => $this->siteKey,
            'formControlId' => $controlId,
            'scriptSrc' => $this->getScriptSrc($onloadCallback),
            'onLoadCallbackName' => $onloadCallback,
        ], \JSON_THROW_ON_ERROR));

        $placeholder->attributes->setData('palmtree-form-captcha', $config);

        if ($this->autoload) {
            $placeholder->classes[] = 'palmtree-captcha-autoload';
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
     * Returns the captcha API script source with an onload callback.
     */
    private function getScriptSrc(string $onloadCallbackName): string
    {
        $url = $this->getScriptUrl();

        parse_str((string)parse_url($url, \PHP_URL_QUERY), $queryArgs);

        $queryArgs['onload'] = $onloadCallbackName;
        $queryArgs['render'] = 'explicit';

        return \sprintf('%s?%s', strtok($url, '?'), http_build_query($queryArgs));
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

            $handle = curl_init($this->getVerifyUrl());

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

            $this->verificationResult[$response] = json_decode($result, true, flags: \JSON_THROW_ON_ERROR);
        }

        return $this->verificationResult[$response];
    }
}
