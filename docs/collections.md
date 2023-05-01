# Collections

The `CollectionType` can be used to add/remove multiple entries of the same field or set of fields:

```php
use Palmtree\Form\FormBuilder;

$builder = (new FormBuilder('collection_example'))
    ->add('name', 'collection', [
        'entry_type'    => 'text',
        'classes'       => ['names-collection']
    ])
    ->add('submit', 'submit');
```

```html
<script src="/path/to/palmtree-form.pkgd.js"></script>
<script>
$(function () {
    $('.names-collection').palmtreeFormCollection({
        minEntries: 1,
        maxEntries: 4,
        labels: {
            add: 'Add person',
            remove: 'Remove person'
        }
    });
});
</script>
```

See the [collection example](/examples/collection) for a more advanced use-case.

[Return to index](index.md)
