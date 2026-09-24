<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\FormBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CheckboxTypeTest extends TestCase
{
    /**
     * @return iterable<string, array{array<string, mixed>, mixed, bool}>
     */
    public static function requiredCheckboxProvider(): iterable
    {
        yield 'default value ticked' => [[], '1', true];
        yield 'boolean-like value ticked' => [[], 'on', true];
        yield 'custom value ticked' => [['value' => 'agree'], 'agree', true];
        yield 'custom value with boolean-like data' => [['value' => 'agree'], 'yes', true];
        yield 'custom value with other data' => [['value' => 'agree'], 'nope', false];
        yield 'custom value with zero' => [['value' => 'agree'], '0', false];
        yield 'not ticked' => [[], null, false];
        yield 'empty' => [[], '', false];
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('requiredCheckboxProvider')]
    public function testRequiredCheckbox(array $options, mixed $value, bool $expected): void
    {
        $form = (new FormBuilder('test'))->add('agree', 'checkbox', $options)->getForm();

        $form->submit($value === null ? [] : ['agree' => $value]);

        $this->assertSame($expected, $form->isValid());
    }

    public function testOptionalCheckboxNotTickedIsValid(): void
    {
        $form = (new FormBuilder('test'))->add('agree', 'checkbox', ['required' => false])->getForm();

        $form->submit([]);

        $this->assertTrue($form->isValid());
    }
}
