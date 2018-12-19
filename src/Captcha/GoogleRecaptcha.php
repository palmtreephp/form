<?php

namespace Palmtree\Form\Captcha;

use Palmtree\Form\Form;
use Palmtree\Html\Element;

class GoogleRecaptcha extends AbstractCaptcha implements CaptchaInterface
{
    const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    const SCRIPT_URL = 'https://www.google.com/recaptcha/api.js';

    /**
     * @var array
     */
    protected static $errorCodes = [
        'missing-input-secret'   => 'The secret parameter is missing.',
        'invalid-input-secret'   => 'The secret parameter is invalid or malformed.',
        'missing-input-response' => 'The response parameter is missing.',
        'invalid-input-response' => 'The response parameter is invalid or malformed.',
    ];

    /**
     * @var string
     */
    protected $secretKey;
    /**
     * @var string
     */
    protected $siteKey;
    /**
     * @var mixed
     */
    protected $ip;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * GoogleRecaptcha constructor.
     *
     * @param string      $siteKey   Site key obtained from Google Recaptcha admin
     * @param string      $secretKey Secret key obtained from Google Recaptcha admin
     * @param bool|string $ip        Client's IP address. Setting to true uses $_SERVER['REMOTE_ADDR']
     */
    public function __construct($siteKey, $secretKey, $ip = true)
    {
        $this->siteKey   = $siteKey;
        $this->secretKey = $secretKey;

        if ($ip === true) {
            $ip = @$_SERVER['REMOTE_ADDR'];
        }

        $this->ip = $ip;
    }

    /**
     * Returns whether the given response was successful.
     *
     * @param string $response The form's 'g-recaptcha-response' field value.
     *
     * @return bool
     */
    public function verify($response)
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
        $controlId = $formControl->getAttribute('id');

        $formControl->removeClass('palmtree-form-control');
        $formControl->addAttribute('hidden');

        // Placeholder Element that actually displays the captcha
        $placeholderId = sprintf('%s_placeholder', $controlId);

        $placeholder = new Element('div.palmtree-form-control.g-recaptcha');
        $placeholder->addAttribute('id', $placeholderId);
        $placeholder->addDataAttribute('name', $formControl->getAttribute('data-name'));
        $formControl->removeAttribute('data-name');

        $placeholder->addDataAttribute('site_key', $this->siteKey);
        $placeholder->addDataAttribute('form_control', $controlId);

        $onloadCallback = sprintf('%s_onload', str_replace('-', '_', $controlId));
        $placeholder->addDataAttribute('script_url', $this->getScriptSrc($onloadCallback));
        $placeholder->addDataAttribute('onload', $onloadCallback);

        $elements = [
            $placeholder,
            $formControl,
        ];

        return $elements;
    }

    /**
     * Returns the recaptcha API script source with an onload callback.
     *
     * @param string $onloadCallbackName
     * @return string
     */
    protected function getScriptSrc($onloadCallbackName)
    {
        $url = static::SCRIPT_URL;

        $queryArgs = [];
        parse_str(parse_url($url, PHP_URL_QUERY), $queryArgs);

        $queryArgs['onload'] = $onloadCallbackName;
        $queryArgs['render'] = 'explicit';

        $url = sprintf('%s?%s', strtok($url, '?'), http_build_query($queryArgs));

        return $url;
    }

    public function getErrorMessage()
    {
        return $this->errors;
    }

    /**
     * @param string $response
     *
     * @return array
     */
    protected function getVerificationResult($response)
    {
        $postFields = [
            'secret'   => $this->secretKey,
            'response' => $response,
        ];

        if ($this->ip) {
            $postFields['remoteip'] = $this->ip;
        }

        $handle = curl_init(self::VERIFY_URL);

        curl_setopt($handle, CURLOPT_POST, count($postFields));
        curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($postFields));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($handle);

        if (!$result || !is_string($result)) {
            return [];
        }

        return json_decode($result, true);
    }
}
