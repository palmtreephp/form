# Plan: Replace ArgParser with Fluent Interface

Remove the `palmtree/argparser` dependency and replace array-based configuration with a fluent interface for `FormBuilder`, while maintaining backward compatibility where practical. The `FormBuilder` will expose chainable methods like `->key('my_form')`, `->method('POST')`, `->htmlValidation(false)` etc., making the API more discoverable and type-safe.

## Steps

1. **Update `FormBuilder` constructor** to accept only `boundData` parameter (removing `$args` array), and add fluent setter methods: `key()`, `method()`, `action()`, `ajax()`, `htmlValidation()`, `encType()`, `fieldWrapper()`, `invalidElementSelector()` that proxy to the underlying `Form` setters, returning `$this` for chaining.

2. **Modify `Form` constructor** in [Form.php](src/Form.php) to remove the `$args` parameter and `parseArgs()` method, remove the `ArgParser` import, and ensure all properties have sensible defaults (e.g., auto-generate `key` if not set).

3. **Update `AbstractType::parseArgs()`** in [Type/AbstractType.php](src/Type/AbstractType.php) to manually process the `$args` array using simple setter calling logic instead of `ArgParser`, since types still accept configuration arrays when added via `FormBuilder->add()`.

4. **Update `AbstractConstraint`** in [Constraint/AbstractConstraint.php](src/Constraint/AbstractConstraint.php) to handle string `$args` as `errorMessage` directly and array `$args` with simple property assignment instead of using `ArgParser`.

5. **Update all examples** (in [examples/](examples/) directory) to use the new fluent interface: replace array-based `FormBuilder` initialization with chainable method calls.

6. **Update documentation** in [docs/](docs/) files (csrf-protection.md, ajax.md, etc.) and [README.md](README.md) to show the new fluent API patterns.

7. **Update tests** in [tests/unit/](tests/unit/) (DataBindingTest.php, NameConverterTest.php) to use the new fluent interface.

8. **Remove palmtree/argparser dependency** from [composer.json](composer.json) and update composer.lock.

## Further Considerations

1. **Backward compatibility**: Should a deprecated constructor variant temporarily support array args with a deprecation notice, or make a clean breaking change? Recommend clean break with major version bump given existing setters already exist. - MAJOR VERSION BUMP

2. **Default form key**: When `key()` is not explicitly called, should we auto-generate a key (e.g., based on a counter or uniqid), or require it to be set explicitly? Recommend auto-generation with prefix like `form_` + counter for convenience. - YES

3. **Boolean toggles**: For boolean methods like `htmlValidation()`, should the method toggle (no param = true) or always require explicit true/false? Recommend optional parameter defaulting to true: `htmlValidation(bool $enabled = true)` for clarity. - YES

