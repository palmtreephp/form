<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\FormBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ChoiceTypeTest extends TestCase
{
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

    public function testInvalidChoiceErrorMessage(): void
    {
        $form = (new FormBuilder('test'))->add('choice', 'choice', ['choices' => ['a' => 'A']])->getForm();

        $form->submit(['choice' => 'z']);

        $this->assertFalse($form->isValid());
        $this->assertSame('The selected value is not a valid choice', $form->getErrors()['choice']);
    }
}
