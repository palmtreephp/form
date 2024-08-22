# Usage with Vite, Webpack and other bundlers.

The recommended way to use Palmtree Form with Vite, Webpack or other bundlers is to install the package via npm (or yarn/bun):

```sh
npm install @palmtree/form --save
```

Then import the relevant parts into your project:

```ts
// src/main.ts
import '@palmtree/form/ajax';
import '@palmtree/form/form-collection';
import '@palmtree/form/recaptcha';
```

Each of the above imports has side effects, namely adding `DOMContentLoaded` event listeners to the document to initialize the relevant features.

[Return to index](index.md)
