# Palmtree Form

PHP Form builder with [Bootstrap](https://getbootstrap.com/) v4 classes, validation, [Google Recaptcha](https://www.google.com/recaptcha/intro/) support and other goodies

## Requirements
* PHP >= 5.6
* jQuery (Only if you want to use Recaptcha and/or AJAX)

## Installation

Use composer to add the package to your dependencies:
```bash
composer require palmtree/form
```

## Usage Example

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
?>

<div class="container">
    <?php echo $form->render(); ?>
</div>


```

```php
<?php

$form->handleRequest();

if($form->isSubmitted() && $form->isValid()) {
    
}
```
