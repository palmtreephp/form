# Constraints

Constraints allow you to validate a field type. The current built in constraints are:

| Constraint                               | Description                                                                       |
|------------------------------------------|-----------------------------------------------------------------------------------|
| [NotBlank](/src/Constraint/NotBlank.php) | Ensures the field is not empty. Allows values of '0'                              |
| [Email](/src/Constraint/Email.php)       | Ensures the field is a valid email address                                        |
| [Number](/src/Constraint/Number.php)     | Ensures the field is numeric and optionally between a range                       |
| [Length](/src/Constraint/Length.php)     | Ensures the field has a minimum and/or maximum length of characters               |
| [Matching](/src/Constraint/Matching.php) | Ensures the field matches another fields value. Useful for password confirmations |

By default, all required fields have a NotBlank constraint.
Email fields have an email constraint and number fields a Number constraint.

## Using Constraints
```php
// Add an age field where the value must be between 18 and 80
$builder->add('age', 'number', [
    'constraints' => [
        new Constraint\Number(['min' => 18, 'max' => 80])
    ]
]);

// Add a password and confirm password field with a minimum length of 8 characters
$builder->add('password', 'repeated', [
    'repeatable_type' => 'password',
    'constraints' => [
        new Constraint\Length(['min' => 8])
    ]
]);

```

You can also implement your own constraints,
they just need to implement the [ConstraintInterface](/src/Constraint/ConstraintInterface.php).

[Return to index](index.md)
