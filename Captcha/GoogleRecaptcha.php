<?php

namespace Palmtree\Form\Captcha;

use Palmtree\Form\Form;
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
        $controlId    = $formControl->getAttribute('id');
        $callbackName = sprintf('%s_callback', str_replace('-', '_', $controlId));

        $formControl->removeClass('form-control');

        // Element that actually displays the captcha
        $element = new Element('div.form-control.g-recaptcha');

        $element->addDataAttribute('sitekey', $this->siteKey)
                ->addDataAttribute('callback', $callbackName);

        $element->addDataAttribute('name', $formControl->getAttribute('data-name'));
        $formControl->removeAttribute('data-name');

        // Callback function to add the response to our hidden input
        $callbackFn = new Element('script');

        $callbackFn->setInnerText(<<<JS
            var $callbackName = function(response) {
                $('#$controlId').val(response);
            };
JS
        );

        // Recaptcha API script
        $script = new Element('script');
        $script->addAttribute('src', static::SCRIPT_URL);

        $elements = [
            $element,
            $callbackFn,
            $script,
            $formControl,
        ];

        $formGroup = new Element($form->getFieldWrapper());

        $formGroup->addChildren($elements);

        return [$formGroup];
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

