<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\Captcha\HoneypotCaptcha;
use Palmtree\Form\Form;
use Palmtree\Form\FormBuilder;
use PHPUnit\Framework\TestCase;

class HoneypotCaptchaTest extends TestCase
{
    public function testFieldIsHiddenAndNotRequired(): void
    {
        $input = $this->renderHoneypot($this->createForm());

        $this->assertFalse($input->hasAttribute('required'));
        $this->assertFalse($input->hasAttribute('placeholder'));
        $this->assertSame('display: none;', $input->getAttribute('style'));
        $this->assertSame('-1', $input->getAttribute('tabindex'));
        $this->assertSame('true', $input->getAttribute('aria-hidden'));
        $this->assertSame('off', $input->getAttribute('autocomplete'));
    }

    public function testFilledFieldIsInvalidWithNeutralMessage(): void
    {
        $form = $this->createForm();

        $form->submit(['website' => 'https://spam.example']);

        $this->assertFalse($form->isValid());
        $this->assertStringNotContainsStringIgnoringCase('honeypot', (new HoneypotCaptcha())->getErrorMessage());
    }

    public function testEmptyFieldIsValid(): void
    {
        $form = $this->createForm();

        $form->submit(['website' => '']);

        $this->assertTrue($form->isValid());
    }

    private function createForm(): Form
    {
        return (new FormBuilder('test'))->add('website', 'captcha', ['captcha' => new HoneypotCaptcha()])->getForm();
    }

    private function renderHoneypot(Form $form): \DOMElement
    {
        $document = new \DOMDocument();
        $document->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' . $form->render() . '</body></html>', \LIBXML_NOERROR);

        $input = $document->getElementById('form_test-website');
        $this->assertInstanceOf(\DOMElement::class, $input);

        return $input;
    }
}
