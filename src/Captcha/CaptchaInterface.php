<?php

namespace Palmtree\Form\Captcha;

use Palmtree\Form\Form;
use Palmtree\Html\Element;

interface CaptchaInterface
{
    public function verify($answer): bool;

    public function getErrorMessage(): string;

    public function getName(): string;

    public function getSlug(): string;

    /** @return Element[] */
    public function getElements(Element $element, Form $form);
}
