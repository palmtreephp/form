<?php

namespace Palmtree\Form\Type;

use Palmtree\Form\Captcha\CaptchaInterface;
use Palmtree\Html\Element;

class CaptchaType extends AbstractType
{
    protected $type = 'captcha';
    protected $errorMessage = 'Please confirm you\'re not a robot';
    /**
     * @var CaptchaInterface $captcha
     */
    protected $captcha;

    public function __construct(array $args = [])
    {
        parent::__construct($args);

        $this->captcha = new $args['captcha']($this);
    }

    public function isValid()
    {
        if (!$this->getForm()->isSubmitted()) {
            return true;
        }

        $value = $this->getData();

        return $this->captcha->verify($value);
    }

    public function getElements()
    {
        $element = $this->getElement();
        $elements = $this->captcha->getElements($element);

        if (!$this->isValid()) {
            $error = new Element('div.form-control-feedback.small');
            $error->setInnerText($this->getErrorMessage());
            $elements[] = $error;
        }

        return $elements;
    }
}
