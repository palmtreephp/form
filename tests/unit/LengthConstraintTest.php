<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\Constraint\Length;
use Palmtree\Form\Constraint\Number;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LengthConstraintTest extends TestCase
{
    /**
     * @return iterable<string, array{array<string, int>, string, bool}>
     */
    public static function lengthProvider(): iterable
    {
        yield 'ascii at max' => [['max' => 3], 'Zoe', true];
        yield 'multibyte at max' => [['max' => 3], 'Zoë', true];
        yield 'cjk at max' => [['max' => 3], '日本語', true];
        yield 'emoji at max' => [['max' => 1], '🌴', true];
        yield 'multibyte over max' => [['max' => 3], 'Zoës', false];
        yield 'multibyte at min' => [['min' => 3], 'ééé', true];
        yield 'multibyte under min' => [['min' => 3], 'éé', false];
    }

    /**
     * @param array<string, int> $options
     */
    #[DataProvider('lengthProvider')]
    public function testCountsCharactersNotBytes(array $options, string $input, bool $expected): void
    {
        $this->assertSame($expected, (new Length($options))->validate($input));
    }

    public function testErrorMessagesStateInclusiveBounds(): void
    {
        $length = new Length(['min' => 2, 'max' => 5]);

        $length->validate('a');
        $this->assertSame('This field must be at least 2 characters', $length->getErrorMessage());

        $length->validate('abcdef');
        $this->assertSame('This field must be at most 5 characters', $length->getErrorMessage());

        $number = new Number(['max' => 10]);

        $this->assertTrue($number->validate(10));
        $this->assertFalse($number->validate(11));
        $this->assertSame('This value must be less than or equal to 10', $number->getErrorMessage());
    }
}
