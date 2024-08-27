# :palm_tree: Palmtree Form

[![License](http://img.shields.io/packagist/l/palmtree/form.svg)](LICENSE)
[![Build](https://img.shields.io/github/actions/workflow/status/palmtreephp/form/build.yaml?branch=master)](https://github.com/palmtreephp/form/actions/workflows/build.yaml)
[![Packagist Version](https://img.shields.io/packagist/v/palmtree/form)](https://packagist.org/packages/palmtree/form)

PHP form builder with [Bootstrap](https://getbootstrap.com/) classes, [validation](docs/constraints.md), [data binding](docs/data-binding.md),
[Google Recaptcha](https://www.google.com/recaptcha/intro/) and [hCaptcha](https://www.hcaptcha.com/) support, plus other goodies.

## Requirements

* PHP >= 8.1

## Installation

Use composer to add the package to your dependencies:

```sh
composer require palmtree/form
```

Optional: Install the NPM package for AJAX form submission, CAPTCHA support and form collections:

```sh
npm install @palmtree/form
```

## Usage Example

#### Build

```php
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Captcha\GoogleRecaptcha;
use Palmtree\Form\Captcha\HCaptcha;

$builder = (new FormBuilder('my_form'))
    ->add('name', 'text', ['error_message' => 'Please enter your name'])
    ->add('email_address', 'email', [
        'help' => 'We will never share your email with anyone',
    ])
    ->add('message', 'textarea', [
        'required' => false,
        'label' => 'Enter your message',
    ])
    ->add('recaptcha', 'captcha', [
        'captcha' => new GoogleRecaptcha('<site_key>', '<secret>'),
        //'captcha' => new HCaptcha('<site_key>', '<secret>'),
    ]);

$builder->add('send_message', 'submit');

$form = $builder->getForm();
// Set $form to some variable accessible in a view

```

#### Render

```html

<div class="container">
    <?= $form->render(); ?>
</div>

<!-- Optional JS for AJAX submissions, CAPTCHA support and form collections -->
<script src="https://unpkg.com/@palmtree/form@6.1.0/dist/palmtree-form.pkgd.min.js"></script>
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
