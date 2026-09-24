<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\Constraint\Length;
use Palmtree\Form\Constraint\NotBlank;
use Palmtree\Form\Constraint\Number;
use Palmtree\Form\FormBuilder;
use PHPUnit\Framework\TestCase;

class RequiredFieldTest extends TestCase
{
    public function testEmptyRequiredFieldReportsRequiredBeforeOtherConstraints(): void
    {
        $form = (new FormBuilder('test'))
            ->add('password', 'password', ['constraints' => [new Length(['min' => 8])]])
            ->add('number', 'number', ['constraints' => [new Number(['min' => 5])]])
            ->add('custom', 'text', ['error_message' => 'Tell us something', 'constraints' => [new Length(['min' => 3])]])
            ->getForm();

        $form->submit(['password' => '', 'number' => '', 'custom' => '']);

        $this->assertFalse($form->isValid());
        $this->assertSame([
            'password' => 'Please fill in this field',
            'number' => 'Please fill in this field',
            'custom' => 'Tell us something',
        ], $form->getErrors());
    }

    public function testFilledFieldReportsOtherConstraints(): void
    {
        $form = (new FormBuilder('test'))
            ->add('password', 'password', ['constraints' => [new Length(['min' => 8])]])
            ->getForm();

        $form->submit(['password' => 'short']);

        $this->assertFalse($form->isValid());
        $this->assertSame(['password' => 'This field must be at least 8 characters'], $form->getErrors());
    }

    public function testNotBlankIsTheFirstConstraint(): void
    {
        $field = (new FormBuilder('test'))
            ->add('name', 'text', ['constraints' => [new Length(['min' => 2])]])
            ->get('name');

        $constraints = $field->getConstraints();

        $this->assertInstanceOf(NotBlank::class, $constraints[0]);
        $this->assertInstanceOf(Length::class, $constraints[1]);
    }
}
