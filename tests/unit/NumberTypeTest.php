<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\IntegerType;
use Palmtree\Form\Type\NumberType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NumberTypeTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string, string, bool}>
     */
    public static function valueProvider(): iterable
    {
        yield 'integer' => [IntegerType::class, '42', true];
        yield 'negative integer' => [IntegerType::class, '-3', true];
        yield 'integer rejects decimal' => [IntegerType::class, '4.5', false];
        yield 'integer rejects text' => [IntegerType::class, 'abc', false];
        yield 'integer rejects overflow' => [IntegerType::class, '99999999999999999999', false];
        yield 'number' => [NumberType::class, '4.5', true];
        yield 'number with exponent' => [NumberType::class, '1e3', true];
        yield 'number rejects text' => [NumberType::class, 'abc', false];
        yield 'number rejects infinity' => [NumberType::class, '1e999', false];
    }

    /**
     * @param class-string $type
     */
    #[DataProvider('valueProvider')]
    public function testValidation(string $type, string $value, bool $expected): void
    {
        $form = (new FormBuilder('test'))->add('field', $type)->getForm();

        $form->submit(['field' => $value]);

        $this->assertSame($expected, $form->isValid());
    }

    public function testErrorMessages(): void
    {
        $form = (new FormBuilder('test'))
            ->add('integer', IntegerType::class)
            ->add('number', NumberType::class)
            ->getForm();

        $form->submit(['integer' => 'abc', 'number' => 'abc']);

        $this->assertFalse($form->isValid());
        $this->assertSame(['integer' => 'Please enter a whole number', 'number' => 'Please enter a number'], $form->getErrors());
    }

    public function testOptionalEmptyValueNormalisesToNull(): void
    {
        $form = (new FormBuilder('test'))
            ->add('integer', IntegerType::class, ['required' => false])
            ->add('number', NumberType::class, ['required' => false])
            ->getForm();

        $form->submit(['integer' => '', 'number' => '']);

        $this->assertTrue($form->isValid());
        $this->assertNull($form->get('integer')->getNormData());
        $this->assertNull($form->get('number')->getNormData());
    }

    public function testValidValuesAreNormalised(): void
    {
        $form = (new FormBuilder('test'))
            ->add('integer', IntegerType::class)
            ->add('number', NumberType::class)
            ->getForm();

        $form->submit(['integer' => '42', 'number' => '4.5']);

        $this->assertSame(42, $form->get('integer')->getNormData());
        $this->assertSame(4.5, $form->get('number')->getNormData());
    }
}
