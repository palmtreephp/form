<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\Captcha\HoneypotCaptcha;
use Palmtree\Form\Constraint\File\Size;
use Palmtree\Form\Constraint\Length;
use Palmtree\Form\FormBuilder;
use PHPUnit\Framework\TestCase;

class UnknownOptionsTest extends TestCase
{
    /** @var list<string> */
    private array $deprecations = [];

    protected function setUp(): void
    {
        set_error_handler(function (int $level, string $message): bool {
            $this->deprecations[] = $message;

            return true;
        }, \E_USER_DEPRECATED);
    }

    protected function tearDown(): void
    {
        restore_error_handler();
    }

    public function testUnknownFieldOptionIsDeprecated(): void
    {
        (new FormBuilder('test'))->add('name', 'text', ['requried' => false]);

        $this->assertSame([
            'Passing the unknown option "requried" to Palmtree\Form\Type\TextType is deprecated. It is ignored, and will throw an exception in the next major version.',
        ], $this->deprecations);
    }

    public function testUnknownConstraintOptionIsDeprecated(): void
    {
        new Length(['maximum' => 5]);

        $this->assertCount(1, $this->deprecations);
        $this->assertStringContainsString('"maximum"', $this->deprecations[0]);
    }

    public function testUnknownFormOptionIsDeprecated(): void
    {
        new FormBuilder(['key' => 'test', 'ajaxx' => true]);

        $this->assertCount(1, $this->deprecations);
        $this->assertStringContainsString('"ajaxx"', $this->deprecations[0]);
    }

    public function testKnownOptionsAreNotDeprecated(): void
    {
        (new FormBuilder(['key' => 'test', 'ajax' => true, 'html_validation' => false]))
            ->add('name', 'text', [
                'label' => 'Name',
                'required' => false,
                'error_message' => 'Required',
                'placeholder' => 'Your name',
                'classes' => ['a'],
                'attr' => ['autocomplete' => 'name'],
                'help' => 'Help',
                'mapped' => false,
                'constraints' => [new Length(['min' => 2, 'max' => 5, 'error_message' => 'Bad'])],
            ])
            ->add('password', 'repeated', ['repeatable_type' => 'password', 'help' => 'Help'])
            ->add('interests', 'choice', ['choices' => ['a' => 'A'], 'multiple' => true])
            ->add('interests_expanded', 'choice', ['choices' => ['a' => 'A'], 'multiple' => true, 'expanded' => true])
            ->add('file', 'file', ['constraints' => [new Size(['max_bytes' => 5]), new Size(['max' => 5])]])
            ->add('names', 'collection', ['entry_type' => 'text', 'entry_options' => ['label' => 'Name'], 'min_entries' => 1])
            ->add('captcha', 'captcha', ['captcha' => new HoneypotCaptcha()])
            ->getForm()
            ->render();

        $this->assertSame([], $this->deprecations);
    }
}
