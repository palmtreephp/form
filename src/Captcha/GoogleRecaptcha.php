<?php

namespace Palmtree\Form\Captcha;

use Palmtree\Form\Form;
use Palmtree\Html\Element;

class GoogleRecaptcha extends AbstractCaptcha implements CaptchaInterface
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    private const SCRIPT_URL = 'https://www.google.com/recaptcha/api.js';

    /** @var array */
    private const ERROR_CODES = [
        'missing-input-secret'   => 'The secret parameter is missing.',
        'invalid-input-secret'   => 'The secret parameter is invalid or malformed.',
        'missing-input-response' => 'The response parameter is missing.',
        'invalid-input-response' => 'The response parameter is invalid or malformed.',
    ];

    /** @var string */
    private $secretKey;
    /** @var string */
    private $siteKey;
    /** @var string */
    private $ip;
    /** @var array */
    private $errors = [];
    /** @var array */
    private $verificationResult = [];

    /**
     * @param string      $siteKey   Site key obtained from Google Recaptcha admin
     * @param string      $secretKey Secret key obtained from Google Recaptcha admin
     * @param bool|string $ip        Client's IP address. Setting to true uses $_SERVER['REMOTE_ADDR']
     */
    public function __construct(string $siteKey, string $secretKey, $ip = true)
    {
        $this->siteKey   = $siteKey;
        $this->secretKey = $secretKey;

        if ($ip === true) {
            $ip = @$_SERVER['REMOTE_ADDR'];
        }

        $this->ip = $ip;
    }

    /**
     * @param string $response The form's 'g-recaptcha-response' field value.
     */
    public function verify($response): bool
    {
        $result = $this->getVerificationResult($response);

        if ($result['success']) {
            return true;
        }

        if (!empty($result['error-codes'])) {
            $this->errors = $result['error-codes'];
        }

        return false;
    }

    public function getElements(Element $formControl, Form $form)
    {
        $controlId = $formControl->attributes['id'];

        unset($formControl->classes['palmtree-form-control']);

        $formControl->attributes->set('hidden');

        // Placeholder Element that actually displays the captcha
        $placeholderId = sprintf('%s_placeholder', $controlId);

        $placeholder = new Element('div.palmtree-form-control.g-recaptcha');

        $placeholder->attributes['id'] = $placeholderId;
        $placeholder->attributes->setData('name', $formControl->attributes['data-name']);

        unset($formControl->attributes['data-name']);

        $placeholder->attributes->setData('site_key', $this->siteKey);
        $placeholder->attributes->setData('form_control', $controlId);

        $onloadCallback = sprintf('%s_onload', str_replace('-', '_', $controlId));
        $placeholder->attributes->setData('script_url', $this->getScriptSrc($onloadCallback));
        $placeholder->attributes->setData('onload', $onloadCallback);

        return [
            $placeholder,
            $formControl,
        ];
    }

    /**
     * Returns the recaptcha API script source with an onload callback.
     */
    protected function getScriptSrc(string $onloadCallbackName): string
    {
        $url = self::SCRIPT_URL;

        $queryArgs = [];
        parse_str(parse_url($url, PHP_URL_QUERY), $queryArgs);

        $queryArgs['onload'] = $onloadCallbackName;
        $queryArgs['render'] = 'explicit';

        $url = sprintf('%s?%s', strtok($url, '?'), http_build_query($queryArgs));

        return $url;
    }

    public function getErrorMessage(): string
    {
        $error = reset($this->errors);

        return self::ERROR_CODES[$error] ?? $error;
    }

    /**
     * @param string $response
     */
    protected function getVerificationResult($response): array
    {
        if (!isset($this->verificationResult[$response])) {
            $postFields = [
                'secret'   => $this->secretKey,
                'response' => $response,
            ];

            if ($this->ip) {
                $postFields['remoteip'] = $this->ip;
            }

            $handle = curl_init(self::VERIFY_URL);

            curl_setopt($handle, CURLOPT_POST, \count($postFields));
            curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($postFields));
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($handle);

            if (!$result || !\is_string($result)) {
                return [];
            }

            $this->verificationResult[$response] = json_decode($result, true);
        }

        return $this->verificationResult[$response];
    }
}
