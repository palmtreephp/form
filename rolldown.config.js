import { defineConfig } from 'rolldown';

const entries = {
    'assets/js/index.ts': 'palmtree-form.pkgd',
    'assets/js/recaptcha.ts': 'recaptcha',
    'assets/js/form-collection.ts': 'form-collection',
};

export default defineConfig(
    Object.entries(entries).map(([input, name]) => ({
        input,
        checks: {
            // The IIFE bundles are loaded for their side effects; their exports aren't exposed as a global.
            missingNameOptionForIifeExport: false,
        },
        output: [
            {
                file: `dist/${name}.js`,
                format: 'iife',
            },
            {
                file: `dist/${name}.min.js`,
                format: 'iife',
                minify: true,
            },
        ],
    })),
);
