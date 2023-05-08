# Rendering Individual Fields

Use the `renderStart`, `renderEnd`, `renderField` and `renderRest` methods for more fine-grained control over how fields are rendered, such as using Bootstrap's grid system:

```html
<div class="container">
    <?= $form->renderStart(); ?>
    <div class="row">
        <div class="col-6">
            <?= $form->renderField('first_name'); ?>
        </div>
        <div class="col-6">
            <?= $form->renderField('last_name'); ?>
        </div>
    </div>
    <?= $form->renderEnd(); ?>
</div>
```

By default, `renderEnd` will render all remaining unrendered fields before rendering the closing </form> tag. To prevent this, pass `false` as the first argument:

```php
<?= $form->renderEnd(false); ?>
```

[Return to index](index.md)
