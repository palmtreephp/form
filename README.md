# :palm_tree: Palmtree Form

PHP Form builder with [Bootstrap](https://getbootstrap.com/) v4 classes, validation, [Google Recaptcha](https://www.google.com/recaptcha/intro/) support and other goodies

## Requirements
* PHP >= 7.1
* jQuery (If you want to use Recaptcha and/or AJAX)

## Installation

Use composer to add the package to your dependencies:
```bash
composer require palmtree/form
```

## Usage Example

#### Build
```php
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Captcha\GoogleRecaptcha;

$builder = new FormBuilder();

$builder
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

## Constraints

Constraints allow you to validate a field type. The current built in constraints are:

| Constraint       | Description |
| ------------- |-------------|
| [NotBlank](src/Constraint/NotBlank.php)  | Ensures the field is not empty. Allows values of '0'
| [Email](src/Constraint/Email.php)        | Ensures the field is a valid email address
| [Number](src/Constraint/Number.php)      | Ensures the field is numeric and optionally between a range
| [Length](src/Constraint/Length.php)      | Ensures the field has a minimum and/or maximum length of characters
| [Match](src/Constraint/Match.php)        | Ensures the field matches another fields value. Useful for password confirmations

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

You can also implement your own constraints, they just need to implement the [ConstraintInterface](src/Constraint/ConstraintInterface.php)

## File Uploads

### UploadedFile Object

When you retrieve a FileType's data from a form, an instance of [UploadedFile](src/UploadedFile.php) will be returned.
This is a small wrapper object around PHPs native uploaded file array.

### File Constraints

The following constraints can be used on the FileType field:

| Constraint       | Description |
| ------------- |-------------|
| [Extension](src/Constraint/File/Extension.php)  | Ensures the file has an allowed extension
| [MimeType](src/Constraint/File/MimeType.php)  | Ensures the file has an allowed mime type
| [Size](src/Constraint/File/MimeType.php)  | Ensures the file size is between an allowed range

See the [file upload example](examples/fileupload/index.php) for usage examples of these constraints

## Shorthand Type Values

Shorthand values for [built in types](src/Type) are determined by lower-casing the class name and removing the "Type" suffix.
For example:

| Class          | Shorthand Value
| -------------  |-------------|
| TextType       | text        |
| NumberType     | number      |
| EmailType      | email      |
| CollectionType | collection  |

## License

Released under the [MIT license](LICENSE)
