<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\Captcha\GoogleRecaptcha;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

#[RequiresPhpExtension('curl')]
class CaptchaVerificationTest extends TestCase
{
    /** @var resource|null */
    private static $server;
    private static string $baseUrl;

    public static function setUpBeforeClass(): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        self::assertIsResource($socket);
        $address = (string)stream_socket_get_name($socket, false);
        fclose($socket);

        self::$baseUrl = "http://$address";

        $router = __DIR__ . '/Fixtures/captcha-verify-server.php';
        $server = proc_open([\PHP_BINARY, '-S', $address, $router], [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes);
        self::assertIsResource($server);
        self::$server = $server;

        for ($i = 0; $i < 50; ++$i) {
            if ($connection = @fsockopen('127.0.0.1', (int)parse_url(self::$baseUrl, \PHP_URL_PORT))) {
                fclose($connection);

                return;
            }

            usleep(100_000);
        }

        self::fail('Captcha verification stub server did not start');
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$server !== null) {
            proc_terminate(self::$server);
            proc_close(self::$server);
        }
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function responseProvider(): iterable
    {
        yield 'success' => ['/success', true];
        yield 'failure' => ['/failure', false];
        yield 'invalid JSON' => ['/invalid-json', false];
        yield 'HTTP error' => ['/server-error', false];
        yield 'unexpected response shape' => ['/unexpected-shape', false];
        yield 'unreachable' => ['/not-found', false];
    }

    #[DataProvider('responseProvider')]
    public function testVerify(string $path, bool $expected): void
    {
        $this->assertSame($expected, $this->createCaptcha($path)->verify('token'));
    }

    public function testFailureErrorCodeIsExposed(): void
    {
        $captcha = $this->createCaptcha('/failure');

        $this->assertFalse($captcha->verify('token'));
        $this->assertSame('The response parameter is invalid or malformed.', $captcha->getErrorMessage());
    }

    public function testSlowResponseTimesOut(): void
    {
        $captcha = $this->createCaptcha('/slow');
        $captcha->setTimeout(1);

        $start = microtime(true);

        $this->assertFalse($captcha->verify('token'));
        $this->assertLessThan(2.5, microtime(true) - $start);
    }

    private function createCaptcha(string $path): GoogleRecaptcha
    {
        $url = self::$baseUrl . $path;

        return new class('site', 'secret', null, $url) extends GoogleRecaptcha {
            public function __construct(string $siteKey, string $secretKey, ?string $ip, private readonly string $verifyUrl)
            {
                parent::__construct($siteKey, $secretKey, $ip);
            }

            public function getVerifyUrl(): string
            {
                return $this->verifyUrl;
            }
        };
    }
}
