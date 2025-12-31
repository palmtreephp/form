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
- Examples and demos

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

## Implementation Details

### Internal Changes

1. **Form.php** - Removed ArgParser dependency, auto-generates keys
2. **FormBuilder.php** - New constructor signature, added fluent methods
3. **AbstractType.php** - Manual snake_case to camelCase conversion for setters
4. **AbstractConstraint.php** - Manual property setting instead of ArgParser

### Composer Changes

The `palmtree/argparser` dependency has been removed from `composer.json`.

## Testing

All existing tests pass with the new implementation:
- ✅ 35 PHPUnit tests passing
- ✅ PHPStan static analysis passing
- ✅ All examples updated and working

## Questions?

If you encounter any issues during migration, please open an issue on GitHub.

