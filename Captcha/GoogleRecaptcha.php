<?php

namespace Palmtree\Form\Captcha;

use Palmtree\Html\Element;

/**
 * Class GoogleRecaptcha
 * @author Andy Palmer
 */
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
            $ip = $_SERVER['REMOTE_ADDR'];
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
        $response = $_REQUEST['g-recaptcha-response'];
        $result = $this->getVerificationResult($response);

        if ($result['success']) {
            return true;
        }

        if (!empty($result['error-codes'])) {
            $this->errors = $result['error-codes'];
        }

        return false;
    }

    /**
     * Returns the recaptcha div tag used to display the captcha form.
     *
     * @return string
     */
    public function getElements(Element $element)
    {
        $elements = [];
        $element  = new Element('div.g-recaptcha.form-control');

        $element->addDataAttribute('sitekey', $this->siteKey);
        $element->addDataAttribute('name', $this->getName());

        $elements[] = $element;

        $script = new Element('script');
        $script->addAttribute('src', static::SCRIPT_URL);

        $elements[] = $script;

        return $elements;
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

