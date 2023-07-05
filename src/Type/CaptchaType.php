<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Form\Captcha\CaptchaInterface;

class CaptchaType extends AbstractType
{
    protected string $type = 'text';
    protected bool $userInput = false;
    protected ?string $errorMessage = 'Please confirm you\'re not a robot';
    private CaptchaInterface $captcha;
    /** @var bool Whether to display errors from the Captcha implementation */
    private bool $captchaErrors = false;

    public function __construct(array $args = [])
    {
        parent::__construct($args);

        $captcha = $args['captcha'];

        if (!$captcha instanceof CaptchaInterface) {
            /** @var CaptchaInterface $captcha */
            $captcha = new $captcha();
        }

        $this->captcha = $captcha;
    }

    public function isValid(): bool
    {
        if (!$this->form->isSubmitted()) {
            return true;
        }

        $result = $this->captcha->verify($this->data);

        if ($this->captchaErrors && $errorMessage = $this->captcha->getErrorMessage()) {
            $this->setErrorMessage($errorMessage);
        }

        return $result;
    }

    public function getElements(): array
    {
        $element = $this->getElement();
        $elements = $this->captcha->getElements($element, $this->form);

        if (!$this->isValid()) {
            $element->classes[] = 'is-invalid';

            $elements[] = $this->form->createInvalidElement()->setInnerText((string)$this->errorMessage);
        }

        return $elements;
    }

    public function setCaptchaErrors(bool $captchaErrors): void
    {
        $this->captchaErrors = $captchaErrors;
    }
}
