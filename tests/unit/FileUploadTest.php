<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\Constraint\ConstraintInterface;
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\CollectionType;
use Palmtree\Form\Type\FileType;
use PHPUnit\Framework\TestCase;

class FileUploadTest extends TestCase
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

    public function testFilePartSubmittedForTextFieldDoesNotError(): void
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

        $this->assertTrue($form->isSubmitted());
    }

    public function testNonArrayFormKeyIsNotSubmitted(): void
    {
        $form = (new FormBuilder('test'))->add('name', 'text')->getForm();

        $_POST = ['form_test' => 'foo'];

        $form->handleRequest();

        $this->assertFalse($form->isSubmitted());
    }
}
