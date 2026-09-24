<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\Constraint\File\Size;
use Palmtree\Form\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FileSizeConstraintTest extends TestCase
{
    /**
     * @return iterable<string, array{array<string, int>, int, bool}>
     */
    public static function sizeProvider(): iterable
    {
        yield 'default minimum rejects empty file' => [[], 0, false];
        yield 'default minimum allows one byte' => [[], 1, true];
        yield 'max_bytes allows equal' => [['max_bytes' => 100], 100, true];
        yield 'max_bytes rejects larger' => [['max_bytes' => 100], 101, false];
        yield 'min_bytes allows equal' => [['min_bytes' => 10], 10, true];
        yield 'min_bytes rejects smaller' => [['min_bytes' => 10], 9, false];
        yield 'max alias rejects larger' => [['max' => 100], 101, false];
        yield 'min alias rejects smaller' => [['min' => 10], 9, false];
    }

    /**
     * @param array<string, int> $options
     */
    #[DataProvider('sizeProvider')]
    public function testSize(array $options, int $size, bool $expected): void
    {
        $this->assertSame($expected, (new Size($options))->validate(self::createFile($size)));
    }

    public function testErrorMessages(): void
    {
        $constraint = new Size(['min_bytes' => 10, 'max_bytes' => 100]);

        $constraint->validate(self::createFile(5));
        $this->assertSame('File size must be at least 10 bytes', $constraint->getErrorMessage());

        $constraint->validate(self::createFile(500));
        $this->assertSame('File size must not exceed 100 bytes', $constraint->getErrorMessage());
    }

    private static function createFile(int $size): UploadedFile
    {
        return new UploadedFile(['name' => 'a.txt', 'size' => (string)$size, 'tmp_name' => '/tmp/a', 'error' => '0']);
    }
}
