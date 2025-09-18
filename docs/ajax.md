# Ajax Submissions

You can submit forms using Ajax to provide a smoother user experience without full page reloads.

## Frontend Setup

To enable Ajax submissions, you must include the Palmtree Form JavaScript library in your HTML:

```html
<script src="https://unpkg.com/@palmtree/form@latest/dist/palmtree-form.pkgd.min.js"></script>
```

Or, if you are [using a bundler like Vite or Webpack](/docs/vite-webpack-and-other-bundlers.md), you can import the module directly:

```ts
import '@palmtree/form/ajax';
```

## Backend Setup

When building your form, just set the `ajax` option set to `true`:

```php
use Palmtree\Form\FormBuilder;
$builder = (new FormBuilder('ajax_example', ['ajax' => true]))
    ->add('name', 'text')
    ->add('message', 'textarea')
    ->add('submit', 'submit', ['label' => 'Send message']);
```

## Handling Ajax Submissions

When a form is submitted via Ajax you must respond with a JSON response. You may use the [JsonResponse helper class](/src/Http/JsonResponse.php) to make this easier:

```php
use Palmtree\Form\Http\JsonResponse;
use Palmtree\Form\Form;

$form->handleRequest();

if ($form->isSubmitted() && Form::isAjaxRequest()) {
    $response = JsonResponse::fromForm($form, successMessage: 'Thank you for your enquiry! We will be in touch soon.');

    if ($form->isValid()) {
        // Process the form data (e.g. send an email, save to database etc)
    }

    // Sends the JSON response with appropriate headers, and exit
    $response->send();
}
```
