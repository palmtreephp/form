<?php

namespace Palmtree\Form\Captcha;

use Palmtree\Html\Element;

interface CaptchaInterface
{
    /**
     * @param mixed $answer
     *
     * @return bool
     */
    public function verify($answer);

    public function getName();

    public function getSlug();

    public function getElements(Element $element);
}
