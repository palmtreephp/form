<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\Constraint\File\Extension;
use Palmtree\Form\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ExtensionConstraintTest extends TestCase
{
    /**
     * @return iterable<string, array{list<string>, string, bool}>
     */
    public static function extensionProvider(): iterable
    {
        yield 'lowercase' => [['jpg', 'png'], 'photo.jpg', true];
        yield 'uppercase file' => [['jpg', 'png'], 'photo.JPG', true];
        yield 'mixed case file' => [['jpg', 'png'], 'photo.Png', true];
        yield 'uppercase allowed list' => [['JPG'], 'photo.jpg', true];
        yield 'not allowed' => [['jpg', 'png'], 'photo.gif', false];
        yield 'double extension' => [['jpg'], 'photo.jpg.php', false];
        yield 'no extension' => [['jpg'], 'photo', false];
    }

    /**
     * @param list<string> $extensions
     */
    #[DataProvider('extensionProvider')]
    public function testExtension(array $extensions, string $filename, bool $expected): void
    {
        $file = new UploadedFile(['name' => $filename, 'size' => '1', 'tmp_name' => '/tmp/a', 'error' => '0']);

        $this->assertSame($expected, (new Extension(['extensions' => $extensions]))->validate($file));
    }
}
