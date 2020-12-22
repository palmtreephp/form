<?php

namespace Palmtree\Form\Type;

use Palmtree\Form\Captcha\CaptchaInterface;

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
            /** @var CaptchaInterface $captcha */
            $captcha = new $captcha();
        }

        $this->captcha = $captcha;

        if ($errorMessage = $this->captcha->getErrorMessage()) {
            $this->setErrorMessage($errorMessage);
        }
    }

    public function isValid(): bool
    {
        if (!$this->form->isSubmitted()) {
            return true;
        }

        return $this->captcha->verify($this->data);
    }

    public function getElements()
    {
        $element  = $this->getElement();
        $elements = $this->captcha->getElements($element, $this->form);

        if (!$this->isValid()) {
            $element->classes[] = 'is-invalid';

            $elements[] = $this->form->createInvalidElement()->setInnerText($this->errorMessage);
        }

        return $elements;
    }
}
