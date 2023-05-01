# CHANGELOG

All notable changes to this project will be documented in this file.

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
