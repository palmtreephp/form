<?php

declare(strict_types=1);

namespace Palmtree\Form\Csrf;

use Palmtree\Form\Exception\CsrfValidationFailedException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Request;

class SameOriginCsrfValidator implements CsrfValidatorInterface
{
    private Request $request;

    public function __construct(
        ?Request $request = null,
        private readonly bool $strict = true,
    ) {
        $this->request = $request ?? Request::createFromGlobals();
    }

    /**
     * Validate CSRF by checking Origin and Referer headers.
     *
     * @throws CsrfValidationFailedException
     */
    public function validate(): void
    {
        if ($this->request->isMethodSafe()) {
            // Safe methods do not require CSRF validation
            return;
        }

        try {
            $isValidOrigin = $this->isValidOrigin();

            if ($isValidOrigin === false || ($this->strict && $isValidOrigin === null)) {
                throw new CsrfValidationFailedException('CSRF validation failed: invalid origin.');
            }
        } catch (SuspiciousOperationException $e) {
            throw new CsrfValidationFailedException('CSRF validation failed: ' . $e->getMessage(), previous: $e);
        }
    }

    public function isValidOrigin(): ?bool
    {
        if ($this->request->headers->has('Sec-Fetch-Site')) {
            return $this->request->headers->get('Sec-Fetch-Site') === 'same-origin';
        }

        $target = $this->request->getSchemeAndHttpHost() . '/';
        $source = 'null';

        foreach (['Origin', 'Referer'] as $header) {
            if (!$this->request->headers->has($header)) {
                continue;
            }

            $source = $this->request->headers->get($header);

            if (str_starts_with($source . '/', $target)) {
                return true;
            }
        }

        return $source === 'null' ? null : false;
    }
}
