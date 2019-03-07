<?php

namespace Palmtree\Form\Type;

use Palmtree\Form\Captcha\CaptchaInterface;
use Palmtree\Html\Element;

class CaptchaType extends AbstractType
{
    protected $type         = 'text';
    protected $userInput    = false;
    protected $errorMessage = 'Please confirm you\'re not a robot';
    /**
     * @var CaptchaInterface
     */
    protected $captcha;

    public function __construct(array $args = [])
    {
        parent::__construct($args);

        $captcha = $args['captcha'];

        if (!$captcha instanceof CaptchaInterface) {
            $captcha = new $captcha();
        }

        $this->captcha = $captcha;

        $errorMessage = $this->captcha->getErrorMessage();

        if ($errorMessage) {
            $this->setErrorMessage($errorMessage);
        }
    }

    public function isValid()
    {
        if (!$this->getForm()->isSubmitted()) {
            return true;
        }

        $value = $this->getData();

        return $this->captcha->verify($value);
    }

    public function getElements(Element $wrapper = null)
    {
        $element  = $this->getElement();
        $elements = $this->captcha->getElements($element, $this->getForm());

        if (!$this->isValid()) {
            $error = $this->getForm()->createInvalidElement();
            $error->setInnerText($this->getErrorMessage());
            $elements[] = $error;
        }

        return $elements;
    }
}
