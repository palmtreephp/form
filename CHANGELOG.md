# CHANGELOG

All notable changes to this project will be documented in this file.

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
