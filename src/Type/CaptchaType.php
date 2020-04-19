<?php

namespace Palmtree\Form\Type;

use Palmtree\Form\Captcha\CaptchaInterface;
use Palmtree\Html\Element;

class CaptchaType extends AbstractType
{
    protected $type         = 'text';
    protected $userInput    = false;
    protected $errorMessage = 'Please confirm you\'re not a robot';
    /** @var CaptchaInterface */
    private $captcha;

    public function __construct(array $args = [])
    {
        parent::__construct($args);

        $captcha = $args['captcha'];

        if (!$captcha instanceof CaptchaInterface) {
            $captcha = new $captcha();
        }

        $this->captcha = $captcha;

        if ($errorMessage = $this->captcha->getErrorMessage()) {
            $this->setErrorMessage($errorMessage);
        }
    }

    public function isValid()
    {
        if (!$this->getForm()->isSubmitted()) {
            return true;
        }

        return $this->captcha->verify($this->getData());
    }

    public function getElements(Element $wrapper = null)
    {
        $element  = $this->getElement();
        $elements = $this->captcha->getElements($element, $this->form);

        if (!$this->isValid()) {
            $element->classes[] = 'is-invalid';

            $error = $this->form->createInvalidElement();
            $error->setInnerText($this->getErrorMessage());
            $elements[] = $error;
        }

        return $elements;
    }
}
