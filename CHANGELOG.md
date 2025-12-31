# CHANGELOG

All notable changes to this project will be documented in this file.

## v6.4.0 - 2025-12-31

* Added support for modern CSRF protection using same-origin validation. See the [CSRF protection docs](/docs/csrf-protection.md) for more info.

## v6.3.0 - 2025-12-02

* Added ability to pass a NameConverter to the FormBuilder to customize how default labels and placeholders are generated from field names e.g. camelCase to Title Case.
  See the [name converters docs](/docs/name-converters.md) for more info.
* Bumped minimum required PHP version to 8.3

## v6.2.3 - 2025-12-02

* Bug fix: Added missing `$type = 'number'` property to `IntegerType` which was causing integer fields to render as text inputs.

## v6.2.2 - 2025-09-18

* Added a [JsonResponse helper class](/src/Http/JsonResponse.php) to return JSON responses for Ajax form submissions
* Added [documentation for Ajax submissions](/docs/ajax.md)

## v6.2.1 - 2025-06-09

* Fixed a bug where the `mapped` property was not being set on AbstractType causing unmapped fields to throw an error

## v6.2.0 - 2025-04-08

* Added support for uninitialized properties in bound objects. Uninitialized properties are now treated as `null`

## v6.1.2 - 2025-03-31

* Fixed a bug where `floats` could not be set on `AbstractType::setData`, causing the `NumberType` to not work properly.

## v6.1.1 - 2025-02-06

* Fixed a bug where constraints were always checked on files even if the file was not required and not uploaded. ([#24](https://github.com/palmtreephp/form/issues/24))

## v6.1.0 - 2024-08-23

* Added [hCaptcha](https://www.hcaptcha.com/) support. You can now use either Google Recaptcha or hCaptcha by passing the appropriate captcha object
  to the `captcha` option in the field configuration. See the [hCaptcha example](/examples/hcaptcha/index.php) for more info.

## v6.0.0 - 2024-08-22

* Refactored all jQuery code to vanilla JavaScript/TypeScript. jQuery is no longer required.
* Collections are now initialized with a data attribute containing json config set within PHP code.
  This means that the collection type can now be initialized without any JavaScript. See
  the [collection docs](/docs/collections.md) for more info
* The JavaScript/Typescript code is now published as an [NPM module](https://www.npmjs.com/package/@palmtree/form) and
  can be imported into your project.
  See the [Vite, Webpack and other bundlers docs](/docs/vite-webpack-and-other-bundlers.md) for more info. You can also
  use the [unpkg CDN](https://unpkg.com/@palmtree/form@6.0.0/dist/palmtree-form.pkgd.min.js) to get the full pacakge:

    ```html
    <script src="https://unpkg.com/@palmtree/form@6.0.0/dist/palmtree-form.pkgd.min.js"></script>
    ```

## v5.0.1 - 2024-07-09

* Updated UMD in JS code for Vite support. In Vite production builds, `module.exports` is defined but `require` is not.
  This change checks that `require` is defined as a function before using it.

## v5.0 - 2024-05-01

* Bumped the minimum required PHP version to 8.1

## v4.5.1 - 2024-02-22

* Added the ability to set [form (help) text](https://getbootstrap.com/docs/5.3/forms/form-control/#form-text) for form controls, e.g.:

    ```php
    $form->add('email', EmailType::class, [
        'help' => "We'll never share your email with anyone."
    ]);
    ```

## v4.5.0 - 2024-01-29

* Bug fix: Fixed data binding not working for collection types ([#13](https://github.com/palmtreephp/form/issues/13))
* Bug fix: Allow passing both shorthand types e.g. `text` to [AbstractType::add()](https://github.com/palmtreephp/form/blob/40d5c14af9b12ac336155f9b32110ebda88ac1db/src/Type/AbstractType.php#L390)
* DX: Generic typing in [TypeLocator](https://github.com/palmtreephp/form/blob/73ee2a75b213a2f2e90ecb2da932259e34909e09/src/TypeLocator.php)

## v4.4.1 - 2023-05-15

* Bug fix: The `required` attribute is now correctly omitted on a select field if the `required` option is set to `false`
  in the `ChoiceType` configuration ([#9](https://github.com/palmtreephp/form/issues/9))

## v4.4 - 2023-05-08

* Added data binding to map form data to an object or an array. Read the [docs page](docs/data-binding.md) for more information

## v4.3.2 - 2023-05-01

* Bug fix: Allow `CollectionType` entry types to be scalar. Previously, the entry type data was required to be an array
  which threw a fatal error if the data was a string for example. (#7)

## v4.3.1 - 2022-09-19

* Added `renderRest` method to render all remaining unrendered fields.

## v4.3 - 2022-07-24

Added functionality to render fields individually with a new `renderField` method. Among other things, this allows
developers to fully utilise Bootstrap's grid system by rendering fields in different columns.

## v4.2 - 2022-02-16

Added support for Bootstrap 5. This was achieved by:

* Adding the `mb-3` class alongside `form-group` to div wrappers
* Adding the `form-label` class to form labels
