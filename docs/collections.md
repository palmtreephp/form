# Collections

The `CollectionType` can be used to add/remove multiple entries of the same field or set of fields:

```php
use Palmtree\Form\FormBuilder;

$builder = (new FormBuilder('collection_example'))
    ->add('names', 'collection', [
        'entry_type'    => 'text',
        'min_entries' => 1,
        'max_entries' => 4,
        'add_label' => 'Add person',
    ])
    ->add('submit', 'submit');
```

See the [collection example](/examples/collection) for a more advanced use-case.

[Return to index](index.md)
