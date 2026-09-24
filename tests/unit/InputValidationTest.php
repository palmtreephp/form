<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\Captcha\GoogleRecaptcha;
use Palmtree\Form\Captcha\HoneypotCaptcha;
use Palmtree\Form\Constraint\ConstraintInterface;
use Palmtree\Form\Constraint\Email;
use Palmtree\Form\Constraint\File\Extension;
use Palmtree\Form\Constraint\Length;
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\CollectionType;
use Palmtree\Form\Type\FileType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

class InputValidationTest extends TestCase
{
    private const array FORGED_UPLOAD = [
        'name' => 'hosts.txt',
        'type' => 'text/plain',
        'size' => '100',
        'tmp_name' => '/etc/hosts',
        'error' => '0',
    ];

    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
    }

    public function testFileDataInRequestBodyIsIgnored(): void
    {
        $form = (new FormBuilder('test'))
            ->add('file', FileType::class)
            ->add('files', CollectionType::class, ['entry_type' => FileType::class])
            ->getForm();

        $_POST = ['form_test' => [
            'file' => self::FORGED_UPLOAD,
            'files' => [self::FORGED_UPLOAD],
        ]];

        $form->handleRequest();

        $this->assertTrue($form->isSubmitted());
        $this->assertNull($form->get('file')->getData());
        $this->assertSame([], $form->get('files')->getData());
        $this->assertFalse($form->isValid());
        $this->assertSame('No file was uploaded', $form->getErrors()['file']);
    }

    public function testFileNotUploadedViaHttpIsInvalidAndConstraintsDoNotRun(): void
    {
        $constraint = new class implements ConstraintInterface {
            public bool $called = false;

            public function validate(mixed $input): bool
            {
                $this->called = true;

                return true;
            }

            public function getErrorMessage(): string
            {
                return '';
            }
        };

        $form = (new FormBuilder('test'))
            ->add('file', FileType::class, ['constraints' => [$constraint]])
            ->getForm();

        $form->submit(['file' => self::FORGED_UPLOAD]);

        $this->assertFalse($form->isValid());
        $this->assertSame('The file could not be uploaded', $form->getErrors()['file']);
        $this->assertFalse($constraint->called);
    }

    public function testRequiredFileMissingIsInvalid(): void
    {
        $form = (new FormBuilder('test'))
            ->add('name', 'text')
            ->add('file', FileType::class)
            ->getForm();

        $form->submit(['name' => 'Bob']);

        $this->assertFalse($form->isValid());
        $this->assertSame('No file was uploaded', $form->getErrors()['file']);
    }

    public function testOptionalFileMissingIsValid(): void
    {
        $form = (new FormBuilder('test'))
            ->add('file', FileType::class, ['required' => false])
            ->getForm();

        $form->submit([]);

        $this->assertTrue($form->isValid());
    }

    public function testMalformedFileDataIsTreatedAsMissing(): void
    {
        $form = (new FormBuilder('test'))
            ->add('file', FileType::class)
            ->getForm();

        $form->submit(['file' => ['name' => ['nested']]]);

        $this->assertNull($form->get('file')->getData());
        $this->assertFalse($form->isValid());
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

    public function testNonArrayFormKeyIsNotSubmitted(): void
    {
        $form = (new FormBuilder('test'))->add('name', 'text')->getForm();

        $_POST = ['form_test' => 'foo'];

        $form->handleRequest();

        $this->assertFalse($form->isSubmitted());
    }

    /**
     * @return iterable<string, array{array<string, mixed>, mixed, bool}>
     */
    public static function choiceProvider(): iterable
    {
        $choices = ['a' => 'A', 'b' => 'B', 'Group' => ['c' => 'C']];

        yield 'valid' => [['choices' => $choices], 'a', true];
        yield 'valid in option group' => [['choices' => $choices], 'c', true];
        yield 'option group label' => [['choices' => $choices], 'Group', false];
        yield 'not a choice' => [['choices' => $choices], 'z', false];
        yield 'array for single choice' => [['choices' => $choices], ['a'], false];
        yield 'multiple valid' => [['choices' => $choices, 'multiple' => true], ['a', 'c'], true];
        yield 'multiple with invalid' => [['choices' => $choices, 'multiple' => true], ['a', 'z'], false];
        yield 'multiple with nested array' => [['choices' => $choices, 'multiple' => true], [['a']], false];
        yield 'expanded not a choice' => [['choices' => $choices, 'expanded' => true], 'z', false];
        yield 'integer keys' => [['choices' => [1 => 'One', 2 => 'Two']], '2', true];
        yield 'optional empty' => [['choices' => $choices, 'required' => false], '', true];
        yield 'optional missing' => [['choices' => $choices, 'required' => false], null, true];
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('choiceProvider')]
    public function testChoiceValidation(array $options, mixed $value, bool $expected): void
    {
        $form = (new FormBuilder('test'))->add('choice', 'choice', $options)->getForm();

        $form->submit($value === null ? [] : ['choice' => $value]);

        $this->assertSame($expected, $form->isValid());
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

        $form->submit(['field' => ['x'], 'field_2' => ['x']]);

        $this->assertFalse($form->isValid());
        $this->assertIsString($form->render());
    }

    public function testConstraintsRejectNonScalarInput(): void
    {
        $this->assertFalse((new Email())->validate(['a@example.org']));
        $this->assertFalse((new Email())->validate(null));
        $this->assertFalse((new Length(['max' => 5]))->validate(new \stdClass()));
        $this->assertTrue((new Length(['max' => 5]))->validate(12345));
        $this->assertFalse((new Extension(['extensions' => ['txt']]))->validate(self::FORGED_UPLOAD));
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
