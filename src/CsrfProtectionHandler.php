<?php

declare(strict_types=1);

namespace Palmtree\Form;

class CsrfProtectionHandler
{
    private const SESSION_KEY_FORMAT = 'palmtree_form_csrf_%s';

    public function __construct()
    {
    }

    public function validateToken(string $identifier, string $token): bool
    {
        $this->ensureSessionStarted();

        $sessionToken = $_SESSION[$this->getSessionKey($identifier)] ?? null;

        if ($sessionToken === null) {
            return false;
        }

        if ($sessionToken !== $token) {
            return false;
        }

        return true;
    }

    public function getToken(string $identifier): string
    {
        $this->ensureSessionStarted();

        $sessionKey = $this->getSessionKey($identifier);

        if (isset($_SESSION[$sessionKey])) {
            return $_SESSION[$sessionKey];
        }

        $token = bin2hex(random_bytes(16));

        $_SESSION[$this->getSessionKey($identifier)] = $token;

        return $token;
    }

    public function clearToken(string $identifier): void
    {
        $this->ensureSessionStarted();

        unset($_SESSION[$this->getSessionKey($identifier)]);
    }

    private function getSessionKey(string $identifier): string
    {
        return sprintf(self::SESSION_KEY_FORMAT, $identifier);
    }

    private function ensureSessionStarted(): void
    {
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            if (!session_start()) {
                throw new \RuntimeException('Cannot enable CSRF protection on a form without session support');
            }
        }
    }
}
