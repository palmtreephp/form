# Palmtree Form

PHP Form builder with [Bootstrap](https://getbootstrap.com/) v4 classes, validation, [Google Recaptcha](https://www.google.com/recaptcha/intro/) support and other goodies

## Requirements
* PHP >= 5.6
* jQuery (If you want to use Recaptcha and/or AJAX)

## Installation

Use composer to add the package to your dependencies:
```bash
composer require palmtree/form
```

## Usage Example

#### Build
```php
<?php
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Captcha\GoogleRecaptcha;

$builder = new FormBuilder();

$builder
    ->add('name', 'text', ['error_message' => 'Please tell me your name'])
    ->add('email_address', 'email')
    ->add('message', 'textarea', ['required' => false])
    ->add('recaptcha', 'captcha', [
        'captcha' => new GoogleRecaptcha('<site_key>', '<secret>'),
    ]);

$builder->add('send_message', 'submit', ['classes' => 'btn btn-primary']);

$form = $builder->getForm();
// Set $form to some variable accessible in a view

```

#### Render
```html
<script src="/path/to/palmtree-form-pkgd.min.js"></script> <!-- Optional -->
<div class="container">
    <?php echo $form->render(); ?>
</div>
```

#### Process
```php
<?php

$form->handleRequest();

if($form->isSubmitted() && $form->isValid()) {
    // Send an email/save to database etc
    $name = $form->getField('name')->getData();
    
    $name = $form->getField('email_address')->
}
```

## Constraints

Constraints allow you to validate a field type. The current built in constraints are:

| [NotBlank](src/Constraint/NotBlank.php) | Ensures the field is not empty. Allows values of '0'        |
| [Email](src/Constraint/Email.php)       | Ensures the field is a valid email address                  |
| [Number](src/Constraint/Number.php)     | Ensures the field is numeric and optionally between a range |

By default, all fields have a NotBlank constraint. 
Email fields have an email constraint and number fields a Number constraint.

## Using Constraints
```php
<?php
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Constraint\Number;

$builder = new FormBuilder();

// Add an age field where the value must be between 18 and 80
$builder->add('age', 'number', [
    'constraints' => [
        (new Number())->setMin(18)->setMax(80)
    ]    
]);

```

You can also implement your own constraints, they just need to implement the [ConstraintInterface](src/Constraint/ConstraintInterface.php)

## License

Released under the [MIT license](LICENSE)
