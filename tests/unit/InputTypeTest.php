<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\Captcha\GoogleRecaptcha;
use Palmtree\Form\Captcha\HoneypotCaptcha;
use Palmtree\Form\Constraint\Email;
use Palmtree\Form\Constraint\File\Extension;
use Palmtree\Form\Constraint\Length;
use Palmtree\Form\FormBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

class InputTypeTest extends TestCase
{
    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>}>
     */
    public static function arrayInputProvider(): iterable
    {
        yield 'text' => ['text', []];
        yield 'email' => ['email', []];
        yield 'textarea' => ['textarea', []];
        yield 'number' => ['number', []];
        yield 'hidden' => ['hidden', []];
        yield 'checkbox' => ['checkbox', []];
        yield 'optional checkbox' => ['checkbox', ['required' => false]];
        yield 'single choice' => ['choice', ['choices' => ['a' => 'A']]];
        yield 'text with length' => ['text', ['constraints' => [new Length(['min' => 2])]]];
        yield 'repeated' => ['repeated', ['repeatable_type' => 'password']];
        yield 'honeypot' => ['captcha', ['captcha' => new HoneypotCaptcha()]];
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('arrayInputProvider')]
    public function testArrayInputIsInvalidForScalarFields(string $type, array $options): void
    {
        $form = (new FormBuilder('test'))->add('field', $type, $options)->getForm();

        $form->submit(['field' => ['a'], 'field_2' => ['a']]);

        $this->assertFalse($form->isValid());
        $this->assertIsString($form->render());
    }

    public function testArrayInputIsValidForMultipleChoice(): void
    {
        $form = (new FormBuilder('test'))
            ->add('field', 'choice', ['choices' => ['a' => 'A'], 'multiple' => true])
            ->getForm();

        $form->submit(['field' => ['a']]);

        $this->assertTrue($form->isValid());
    }

    public function testFilePartSubmittedForTextFieldIsInvalid(): void
    {
        $form = (new FormBuilder('test'))
            ->add('name', 'text', ['required' => false])
            ->getForm();

        $_POST = ['form_test' => ['name' => 'Bob']];
        $_FILES = ['form_test' => [
            'name' => ['name' => 'a.txt'],
            'type' => ['name' => 'text/plain'],
            'size' => ['name' => 1],
            'tmp_name' => ['name' => '/tmp/php123'],
            'error' => ['name' => 0],
        ]];

        $form->handleRequest();

        $this->assertFalse($form->isValid());
        $this->assertSame('This value is not valid', $form->getErrors()['name']);
    }

    public function testConstraintsRejectWrongInputTypes(): void
    {
        $this->assertFalse((new Email())->validate(['a@example.org']));
        $this->assertFalse((new Email())->validate(null));
        $this->assertFalse((new Length(['max' => 5]))->validate(new \stdClass()));
        $this->assertTrue((new Length(['max' => 5]))->validate(12345));
        $this->assertFalse((new Extension(['extensions' => ['txt']]))->validate(['name' => 'a.txt']));
    }

    public function testMatchingAllowsBothEmpty(): void
    {
        $form = (new FormBuilder('test'))
            ->add('password', 'repeated', ['repeatable_type' => 'password', 'required' => false])
            ->getForm();

        $form->submit([]);

        $this->assertTrue($form->isValid());
    }

    public function testHoneypotRequiresEmptyString(): void
    {
        $captcha = new HoneypotCaptcha();

        $this->assertTrue($captcha->verify(''));
        $this->assertFalse($captcha->verify('spam'));
        $this->assertFalse($captcha->verify(null));
        $this->assertFalse($captcha->verify(['']));
    }

    #[RequiresPhpExtension('curl')]
    public function testRecaptchaRejectsNonStringOrEmptyInputWithoutVerifying(): void
    {
        $captcha = new GoogleRecaptcha('site', 'secret');

        $this->assertFalse($captcha->verify(['token']));
        $this->assertFalse($captcha->verify(null));
        $this->assertFalse($captcha->verify(''));
    }
}
