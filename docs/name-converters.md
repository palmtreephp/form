# Name Converters

By default, this library uses a snake_case to Title Case conversion for generating
default labels and placeholders from field names. For example, a field named `first_name`
will have a default label of `First Name` and a default placeholder of `Enter your first name`.

If your field names (or object properties, if using data binding) use a different naming convention such as camelCase,
this may not produce the desired results. For example, a field named `firstName` will have a default label of `Firstname` and a default
placeholder of `Enter your firstname`.

To customize how default labels and placeholders are generated from field names,
you may pass a `NameConverterInterface` instance to the FormBuilder:

```php
use Palmtree\Form\FormBuilder;
use Palmtree\Form\NameConverter\NameConverterInterface;
use Palmtree\NameConverter\CamelCaseToHumanNameConverter;

$builder = new FormBuilder('some_form_key');
$builder->setNameConverter(new CamelCaseToHumanNameConverter());

$builder->add('firstName', 'text'); // Default Label: "First Name", Default Placeholder: "Enter your first name"
$builder->add('emailAddress', 'email'); // Default Label: "Email Address", Default Placeholder: "Enter your email address"
```

This library uses [palmtree/nameconverter](https://github.com/palmtreephp/nameconverter) for name conversion.
You can use any of the [built-in converters](https://github.com/palmtreephp/nameconverter/tree/c79bb8a55e0a8adba490855d66713ba6fc48fe8f/src) provided by that library,
or implement your own by implementing the [`NameConverterInterface`](https://github.com/palmtreephp/nameconverter/blob/c79bb8a55e0a8adba490855d66713ba6fc48fe8f/src/NameConverterInterface.php).

[Return to index](index.md)
