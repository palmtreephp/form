# :palm_tree: Palmtree Form

[![License](http://img.shields.io/packagist/l/palmtree/form.svg)](LICENSE)
[![Build](https://img.shields.io/github/actions/workflow/status/palmtreephp/form/build.yaml?branch=master)](https://github.com/palmtreephp/form/actions/workflows/build.yaml)
[![Packagist Version](https://img.shields.io/packagist/v/palmtree/form)](https://packagist.org/packages/palmtree/form)

PHP form builder with [Bootstrap](https://getbootstrap.com/) v5 and v4 classes, validation, data binding, [Google Recaptcha](https://www.google.com/recaptcha/intro/) support and other goodies

## Requirements
* PHP >= 7.1
* jQuery (If you want to use Recaptcha and/or AJAX submissions)

## Installation

Use composer to add the package to your dependencies:
```sh
composer require palmtree/form
```

## Usage Example

#### Build
```php
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Captcha\GoogleRecaptcha;

$builder = (new FormBuilder('my_form'))
    ->add('name', 'text', ['error_message' => 'Please enter your name'])
    ->add('email_address', 'email')
    ->add('message', 'textarea', [
        'required' => false,
        'label' => 'Enter your message',
    ])
    ->add('recaptcha', 'captcha', [
        'captcha' => new GoogleRecaptcha('<site_key>', '<secret>'),
    ]);

$builder->add('send_message', 'submit');

$form = $builder->getForm();
// Set $form to some variable accessible in a view

```

#### Render
```html
<script src="/path/to/palmtree-form.pkgd.min.js"></script> <!-- Optional -->
<div class="container">
    <?= $form->render(); ?>
</div>
```

#### Process
```php
$form->handleRequest();

if ($form->isSubmitted() && $form->isValid()) {
    // Send an email/save to database etc
    $name = $form->get('name')->getData();
}
```

See the [examples](examples) directory for examples using AJAX, file uploads, collections and more.

## Documentation

[View the documentation](docs/index.md) for more advanced usage.

## Examples

The simplest way to run the examples is run the serve.sh script. This script starts a small PHP Docker container
and serves the examples using PHP's built-in web server.

```sh
./examples/serve.sh
```

## License

Released under the [MIT license](LICENSE)
