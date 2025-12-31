# CSRF Protection

Cross-Site Request Forgery (CSRF) protection is a security measure for preventing unauthorized form submissions from third-party sites.
This library provides built-in CSRF protection using a same-origin verification strategy.

## Overview

The library protects against CSRF attacks by validating that form submissions originate from the same site that served the form.
This is accomplished by checking HTTP headers that modern browsers send with requests:

- **Sec-Fetch-Site**: The preferred modern header that explicitly indicates whether the request originated from the same site, a cross-site request, or other origins
- **Origin** / **Referer**: Headers used as fallback methods to verify the request's source

## Enabling CSRF Protection

To enable CSRF protection for your form, call the `enableCsrf()` method on your `FormBuilder`:

```php
<?php

use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\TextType;

// Optional but recommended: Configure trusted hosts
// See https://symfony.com/doc/current/reference/configuration/framework.html#trusted-hosts
\Symfony\Component\HttpFoundation\Request::setTrustedHosts(['^example\.org$']);

$builder = new FormBuilder([
    'key'    => 'contact_form',
    'method' => 'POST',
]);

// Enable CSRF protection with default settings (strict mode enabled)
$builder->enableCsrf();

$builder
    ->add('name', TextType::class, ['label' => 'Name'])
    ->add('email', 'email', ['label' => 'Email'])
    ->add('message', 'textarea', ['label' => 'Message'])
    ->add('submit', 'submit');

$form = $builder->getForm();
```

> [!NOTE]
> If your site is behind a load balancer or reverse proxy, you may also need to call [`Request::setTrustedProxies()`](https://symfony.com/doc/current/deployment/proxies.html#solution-settrustedproxies) to ensure correct handling of headers.

### Method Signature

```php
public function enableCsrf(bool $strict = true, ?Request $request = null): self
```

**Parameters:**
- `$strict` (bool, default: `true`): When `true`, the validator requires explicit origin validation. A missing origin header in non-modern browsers will be treated as invalid in strict mode. When `false`, missing headers are tolerated.
- `$request` (Request|null, default: `null`): An optional Symfony HttpFoundation `Request` object. If not provided, one is automatically created from superglobals.

### Strict Mode

By default, CSRF protection runs in strict mode. This means:

- **Strict mode enabled** (`true`): All requests must have verifiable origin information. If the browser doesn't send origin headers, the request is rejected.
- **Strict mode disabled** (`false`): The validator will accept requests even if no origin headers are present, falling back to assuming they are safe.

For most modern web applications, strict mode is recommended as it provides the strongest protection. You might disable strict mode only if you need to support very old browsers or legacy clients that don't send origin headers.

```php
// Permissive mode (less secure, for legacy support)
$builder->enableCsrf(strict: false);
```

### Validation Logic

The `SameOriginCsrfValidator` checks origins in the following order:

```
1. If Sec-Fetch-Site header exists:
   └─ Accept if value is 'same-origin'

2. If Sec-Fetch-Site is absent:
   ├─ Check Origin header
   ├─ Check Referer header
   └─ Compare against request's scheme and host
      ├─ Accept if header matches (same-origin request)
      ├─ Reject if header indicates different origin
      └─ In strict mode: Reject if no headers present
                In permissive mode: Accept if no headers present
```

## Custom CSRF Validators

If you need custom CSRF validation logic, you can implement the `CsrfValidatorInterface` and set it on the form:

```php
<?php

use Palmtree\Form\Csrf\CsrfValidatorInterface;
use Palmtree\Form\Exception\CsrfValidationFailedException;

class CustomCsrfValidator implements CsrfValidatorInterface
{
    public function validate(): void
    {
        // Your custom validation logic here
        if (!$this->isValidToken()) {
            throw new CsrfValidationFailedException('Custom CSRF validation failed');
        }
    }

    private function isValidToken(): bool
    {
        // Implement your validation logic
        return true;
    }
}

// Use the custom validator
$builder = new FormBuilder(['key' => 'my_form']);
$builder->setCsrfValidator(new CustomCsrfValidator());
```

## Important Security Notes

1. **HTTPS Recommended**: While same-origin validation works with HTTP, using HTTPS is strongly recommended to prevent header manipulation and other attacks.

2. **Browser Support**: The `Sec-Fetch-Site` header is supported in all modern browsers. Older browsers may not send this header, which is why the validator has fallback logic for `Origin` and `Referer` headers.

3. **Trusted Hosts**: When using Symfony's Request class, ensure you configure trusted hosts appropriately to prevent Host Header injection attacks:
   ```php
   \Symfony\Component\HttpFoundation\Request::setTrustedHosts(['localhost', 'example.com']);
   ```

4. **Safe Methods**: The validator automatically skips validation for safe HTTP methods (GET, HEAD, OPTIONS, TRACE). CSRF protection is only applied to state-changing requests (POST, PUT, PATCH, DELETE).

## Further Reading

- [Protecting Against CSRF in 2025](https://words.filippo.io/csrf/#protecting-against-csrf-in-2025)
- [MDN Web Docs: Cross-Site Request Forgery (CSRF)](https://developer.mozilla.org/en-US/docs/Web/Security/Attacks/CSRF)
