<?php

namespace Palmtree\Form\Captcha;

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

    public function getElements();
}
