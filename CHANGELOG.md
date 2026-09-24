# CHANGELOG

All notable changes to this project will be documented in this file.

## v6.5.0 - 2026-09-24

This release fixes several security issues. All users should upgrade.

* **Security:** Fixed reflected XSS where submitted values, labels, help text, choice labels and error messages were rendered without HTML escaping.
  Requires `palmtree/html` v6, which escapes all inner text and attribute values. HTML passed in labels, help text or other text options is now displayed as text.
* **Security:** File fields only accept data from `$_FILES`. A client could previously submit a forged upload array in the request body with an arbitrary
  `tmp_name` (e.g. `/etc/passwd`). `FileType` also rejects any file that `is_uploaded_file()` does not recognise before running constraints.
* **Security:** `ChoiceType` rejects submitted values that are not one of its choices.
* **Security:** Fields that expect a single value are invalid when an array is submitted, and constraints and captchas fail validation for input of the wrong type
  instead of throwing a `TypeError`.
* reCAPTCHA and hCaptcha verification requests time out after 3 seconds to connect and 5 seconds in total, configurable with `setTimeout()`. Verification fails
  instead of throwing for a non-200 response, invalid JSON or an unexpected response.
* A form that fails CSRF validation is submitted and invalid, so `isSubmitted()` returns `true`. The submitted values are kept for redisplay but not mapped
  to bound data, and `JsonResponse::fromForm()` returns the CSRF error rather than throwing `NotSubmittedException`. The message is available as `Form::CSRF_ERROR_MESSAGE`.
  See [handling failed validation](/docs/csrf-protection.md#handling-failed-validation).
* `JsonResponse::fromForm()` uses the form's error message, when set, as the response message.
* A required `FileType` field is invalid when no file data is submitted at all.
* Fixed the `Size` file constraint ignoring the `'max'` option used in the examples, which meant no size limit was applied. If you copied an example,
  your limit is now enforced. The options are `min_bytes`/`max_bytes`, with `min`/`max` accepted as aliases, and error messages state the inclusive bounds.
* The `Length` constraint counts characters rather than bytes, so multibyte input such as `Zoë` no longer fails a maximum it is within.
  `symfony/polyfill-mbstring` is now a direct dependency. The `Length` and `Number` maximum error messages state that the bound is inclusive.
* The JavaScript package inserts error messages, alert messages and the collection add label as text rather than HTML.
* Fixed a textarea with a value of `"0"` rendering empty.
* Added `UploadedFile::isUploaded()` and `UploadedFile::isUploadedFileArray()`.
* The JavaScript bundles in `dist/` are built with rolldown and target ES2022, which requires Safari/iOS 14 or later.
* Fixed the data-binding example referencing a missing test fixture.

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
