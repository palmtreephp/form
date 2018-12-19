<?php

namespace Palmtree\Form\Captcha;

use Palmtree\Form\Form;
use Palmtree\Html\Element;

interface CaptchaInterface
{
    /**
     * @param mixed $answer
     *
     * @return bool
     */
    public function verify($answer);

    /**
     * @return string
     */
    public function getErrorMessage();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getSlug();

    /**
     * @param Element $element
     * @param Form    $form
     *
     * @return array
     */
    public function getElements(Element $element, Form $form);
}
