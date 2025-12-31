<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit\Csrf;

use Palmtree\Form\Csrf\SameOriginCsrfValidator;
use Palmtree\Form\Exception\CsrfValidationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class SameOriginCsrfValidatorTest extends TestCase
{
    public function testValidateWithInvalidOriginThrowsException(): void
    {
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'https://malicious.com',
        ]);

        $validator = new SameOriginCsrfValidator($request);

        $this->expectException(CsrfValidationFailedException::class);
        $this->expectExceptionMessage('CSRF validation failed: invalid origin.');
        $validator->validate();
    }

    public function testValidateWithValidOrigin(): void
    {
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'https://example.com',
            'HTTPS' => 'on',
        ]);

        $validator = new SameOriginCsrfValidator($request);

        $validator->validate();
        $this->assertTrue(true); // No exception thrown
    }

    public function testIsValidOriginWithMatchingOriginHeader(): void
    {
        $request = Request::create('https://example.com/path', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'https://example.com',
        ]);

        $validator = new SameOriginCsrfValidator($request);
        $this->assertTrue($validator->isValidOrigin());
    }

    public function testIsValidOriginWithMatchingRefererHeader(): void
    {
        $request = Request::create('https://example.com/path', 'POST', [], [], [], [
            'HTTP_REFERER' => 'https://example.com/previous',
        ]);

        $validator = new SameOriginCsrfValidator($request);
        $this->assertTrue($validator->isValidOrigin());
    }

    public function testIsValidOriginWithDifferentOrigin(): void
    {
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'https://malicious.com',
        ]);

        $validator = new SameOriginCsrfValidator($request);
        $this->assertFalse($validator->isValidOrigin());
    }

    public function testIsValidOriginWithDifferentReferer(): void
    {
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_REFERER' => 'https://malicious.com/page',
        ]);

        $validator = new SameOriginCsrfValidator($request);
        $this->assertFalse($validator->isValidOrigin());
    }

    public function testIsValidOriginWithNoHeaders(): void
    {
        $request = Request::create('https://example.com', 'POST');

        $validator = new SameOriginCsrfValidator($request);
        $this->assertNull($validator->isValidOrigin());
    }

    public function testIsValidOriginWithNullOrigin(): void
    {
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'null',
        ]);

        $validator = new SameOriginCsrfValidator($request);
        $this->assertNull($validator->isValidOrigin());
    }

    public function testIsValidOriginPreferOriginOverReferer(): void
    {
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'https://example.com',
            'HTTP_REFERER' => 'https://malicious.com',
        ]);

        $validator = new SameOriginCsrfValidator($request);
        $this->assertTrue($validator->isValidOrigin());
    }

    public function testIsValidOriginWithHttpRequest(): void
    {
        $request = Request::create('http://example.com', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'http://example.com',
        ]);

        $validator = new SameOriginCsrfValidator($request);
        $this->assertTrue($validator->isValidOrigin());
    }

    public function testIsValidOriginWithSecFetchSiteSameOrigin(): void
    {
        // Test that Sec-Fetch-Site header is checked and returns true for 'same-origin'
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_SEC_FETCH_SITE' => 'same-origin',
        ]);

        $validator = new SameOriginCsrfValidator($request);
        $result = $validator->isValidOrigin();
        $this->assertTrue($result);
    }

    public function testIsValidOriginWithSecFetchSiteCrossOrigin(): void
    {
        // Test that Sec-Fetch-Site header returns false for non-same-origin values
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_SEC_FETCH_SITE' => 'cross-site',
        ]);

        $validator = new SameOriginCsrfValidator($request);
        $result = $validator->isValidOrigin();
        $this->assertFalse($result);
    }

    public function testIsValidOriginWithSecFetchSiteSameSite(): void
    {
        // Test that Sec-Fetch-Site header returns false for 'same-site' (not same-origin)
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_SEC_FETCH_SITE' => 'same-site',
        ]);

        $validator = new SameOriginCsrfValidator($request);
        $result = $validator->isValidOrigin();
        $this->assertFalse($result);
    }

    public function testIsValidOriginSecFetchSiteTakesPrecedence(): void
    {
        // Test that Sec-Fetch-Site header takes precedence over Origin header
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_SEC_FETCH_SITE' => 'same-origin',
            'HTTP_ORIGIN' => 'https://malicious.com',
        ]);

        $validator = new SameOriginCsrfValidator($request);
        $result = $validator->isValidOrigin();
        // Sec-Fetch-Site is checked first, so it should return true
        $this->assertTrue($result);
    }

    public function testValidateWithStrictTrueAndNullOriginThrowsException(): void
    {
        // When strict=true (default) and isValidOrigin() returns null, validation should fail
        $request = Request::create('https://example.com', 'POST');

        $validator = new SameOriginCsrfValidator($request, strict: true);

        $this->expectException(CsrfValidationFailedException::class);
        $this->expectExceptionMessage('CSRF validation failed: invalid origin.');
        $validator->validate();
    }

    public function testValidateWithStrictFalseAndNullOriginPasses(): void
    {
        // When strict=false and isValidOrigin() returns null, validation should pass
        $request = Request::create('https://example.com', 'POST');

        $validator = new SameOriginCsrfValidator($request, strict: false);

        $validator->validate();
        $this->assertTrue(true); // No exception thrown
    }

    public function testValidateWithStrictTrueAndNullOriginFromNullHeaderThrowsException(): void
    {
        // When strict=true and Origin header is 'null', isValidOrigin() returns null, validation should fail
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'null',
        ]);

        $validator = new SameOriginCsrfValidator($request, strict: true);

        $this->expectException(CsrfValidationFailedException::class);
        $this->expectExceptionMessage('CSRF validation failed: invalid origin.');
        $validator->validate();
    }

    public function testValidateWithStrictFalseAndNullOriginFromNullHeaderPasses(): void
    {
        // When strict=false and Origin header is 'null', isValidOrigin() returns null, validation should pass
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'null',
        ]);

        $validator = new SameOriginCsrfValidator($request, strict: false);

        $validator->validate();
        $this->assertTrue(true); // No exception thrown
    }

    public function testValidateWithTrustedHostsAndValidOrigin(): void
    {
        // Test that validation works correctly with trusted hosts set
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'https://example.com',
        ]);

        Request::setTrustedHosts(['example.com']);

        $validator = new SameOriginCsrfValidator($request);

        $validator->validate();
        $this->assertTrue(true); // No exception thrown
    }

    public function testValidateWithTrustedHostsAndInvalidOrigin(): void
    {
        // Test that validation fails with untrusted origin even with trusted hosts set
        $request = Request::create('https://example.com', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'https://untrusted.com',
        ]);

        Request::setTrustedHosts(['example.com']);

        $validator = new SameOriginCsrfValidator($request);

        $this->expectException(CsrfValidationFailedException::class);
        $validator->validate();
    }

    public function testValidateWithMultipleTrustedHostsAndValidOrigin(): void
    {
        // Test with multiple trusted hosts
        $request = Request::create('https://api.example.com', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'https://api.example.com',
        ]);

        Request::setTrustedHosts(['example.com', 'api.example.com', 'cdn.example.com']);

        $validator = new SameOriginCsrfValidator($request);

        $validator->validate();
        $this->assertTrue(true); // No exception thrown
    }

    public function testValidateWithTrustedHostsWildcardAndValidOrigin(): void
    {
        // Test with wildcard pattern in trusted hosts
        $request = Request::create('https://sub.example.com', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'https://sub.example.com',
        ]);

        Request::setTrustedHosts(['.example.com']);

        $validator = new SameOriginCsrfValidator($request);

        $validator->validate();
        $this->assertTrue(true); // No exception thrown
    }

    public function testValidateWithTrustedHostsAndRefererHeader(): void
    {
        // Test validation with Referer header when trusted hosts are set
        $request = Request::create('https://example.com/form', 'POST', [], [], [], [
            'HTTP_REFERER' => 'https://example.com/page',
        ]);

        Request::setTrustedHosts(['example.com']);

        $validator = new SameOriginCsrfValidator($request);

        $validator->validate();
        $this->assertTrue(true); // No exception thrown
    }

    public function testValidateWithTrustedHostsAndInvalidReferer(): void
    {
        // Test validation fails with invalid Referer even with trusted hosts set
        $request = Request::create('https://example.com/form', 'POST', [], [], [], [
            'HTTP_REFERER' => 'https://malicious.com/attack',
        ]);

        Request::setTrustedHosts(['example.com']);

        $validator = new SameOriginCsrfValidator($request);

        $this->expectException(CsrfValidationFailedException::class);
        $validator->validate();
    }

    public function testValidateWithRequestHostNotInTrustedHosts(): void
    {
        // Test that validation fails when the request host itself is not in trusted hosts
        $request = Request::create('https://untrusted.com/form', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'https://untrusted.com',
        ]);

        Request::setTrustedHosts(['example.com', 'api.example.com']);

        $validator = new SameOriginCsrfValidator($request);

        $this->expectException(CsrfValidationFailedException::class);
        $this->expectExceptionMessage('Untrusted Host "untrusted.com"');
        $validator->validate();
    }
}
