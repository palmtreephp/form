# Migration Guide: Array-based to Fluent API

This guide helps you migrate from the array-based FormBuilder configuration to the new fluent interface.

## Overview

The library has been refactored to remove the `palmtree/argparser` dependency and introduce a fluent interface for `FormBuilder` configuration. This makes the API more discoverable, type-safe, and IDE-friendly.

## Breaking Changes

### FormBuilder Constructor

**Before:**
```php
$builder = new FormBuilder([
    'key' => 'my_form',
    'method' => 'POST',
    'html_validation' => false,
    'ajax' => true,
    'action' => 'submit.php',
    'enc_type' => 'multipart/form-data',
]);
```

**After:**
```php
$builder = (new FormBuilder())
    ->key('my_form')
    ->method('POST')
    ->htmlValidation(false)
    ->ajax()
    ->action('submit.php')
    ->encType('multipart/form-data');
```

### With Bound Data

**Before:**
```php
$builder = new FormBuilder([
    'key' => 'my_form',
    'method' => 'POST',
], $person);
```

**After:**
```php
$builder = new FormBuilder($person);
// Or with explicit key:
$builder = (new FormBuilder($person))
    ->key('my_form');
```

### Auto-generated Form Keys

If you don't specify a key, one will be auto-generated:

```php
$builder = new FormBuilder(); // key will be 'form_1', 'form_2', etc.
```

**When to use explicit keys:**
- When you need a specific, predictable form key for testing or debugging
- When you have multiple forms on the same page and need to differentiate them in JavaScript
- When integrating with third-party libraries that expect specific form IDs

**When auto-generated keys are fine:**
- Simple forms with no external dependencies
- Most common use cases where the form key doesn't matter

### Form Keys Are Used As-Is

`key()` uses the key exactly as given. The array configuration prefixed it with `form_`, so element IDs and submitted
field names change:

```php
// Before: form ID "form_contact", fields named "form_contact[email]", IDs like "form_contact-email"
$builder = new FormBuilder(['key' => 'contact']);

// After: form ID "contact", fields named "contact[email]", IDs like "contact-email"
$builder = (new FormBuilder())->key('contact');
```

Update any CSS, JavaScript or test selectors that reference the old IDs, or pass the prefixed key to keep them:
`->key('form_contact')`.

### HTML5 Validation Is Off By Default

Forms render with the `novalidate` attribute unless you opt in to browser validation with `->htmlValidation()`.

## Available Fluent Methods

All FormBuilder configuration is now done via fluent methods:

- `->key(string $key)` - Set the form key
- `->method(string $method)` - Set HTTP method (GET, POST, etc.) - **Default: 'POST'**
- `->action(string $action)` - Set form action URL
- `->ajax(bool $ajax = true)` - Enable/disable AJAX submissions - **Default: false**
- `->htmlValidation(bool $enabled = true)` - Enable/disable HTML5 validation - **Default: false**
- `->encType(string $encType)` - Set encoding type
- `->fieldWrapper(string $fieldWrapper)` - Set field wrapper selector
- `->invalidElementSelector(string $selector)` - Set invalid element selector

### Default Values

You only need to call these methods when you want to override the defaults:

```php
// These are equivalent:
$builder = new FormBuilder();

$builder = (new FormBuilder())
    ->method('POST')
    ->htmlValidation(false)
    ->ajax(false);
```

Most forms can be created with minimal configuration:

```php
// Simple form with all defaults
$builder = new FormBuilder();
$builder->add('name', 'text')
    ->add('submit', 'submit');

// AJAX form - only specify what's different
$builder = (new FormBuilder())
    ->ajax()
    ->add('email', 'email')
    ->add('submit', 'submit');
```

## What Hasn't Changed

### Field Type Configuration

Field types still use array-based configuration when added:

```php
$builder->add('email', 'email', [
    'required' => true,
    'label' => 'Email Address',
    'help' => 'We will never share your email',
]);
```

### Constraint Configuration

Constraints still accept array or string parameters:

```php
use Palmtree\Form\Constraint\Length;

// String shorthand for error message
$builder->add('password', 'password', [
    'constraints' => [
        new Length('Password must be at least 8 characters'),
    ],
]);

// Array for detailed configuration
$builder->add('password', 'password', [
    'constraints' => [
        new Length([
            'min' => 8,
            'max' => 100,
            'error_message' => 'Password must be between 8 and 100 characters',
        ]),
    ],
]);
```

## Questions?

If you encounter any issues during migration, please open an issue on GitHub.

