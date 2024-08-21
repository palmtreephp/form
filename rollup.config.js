import terser from '@rollup/plugin-terser';
import typescript from '@rollup/plugin-typescript';

export default [
    {
        input: 'assets/js/index.ts',
        output: [
            {
                file: 'dist/palmtree-form.pkgd.js',
                format: 'iife',
            },
            {
                file: 'dist/palmtree-form.pkgd.min.js',
                format: 'iife',
                plugins: [terser()]
            }
        ],
        plugins: [typescript()]
    },
    {
        input: 'assets/js/recaptcha.ts',
        output: [
            {
                file: 'dist/recaptcha.js',
                format: 'iife',
            },
            {
                file: 'dist/recaptcha.min.js',
                format: 'iife',
                plugins: [terser()]
            }
        ],
        plugins: [typescript()]
    },
    {
        input: 'assets/js/form-collection.ts',
        output: [
            {
                file: 'dist/form-collection.js',
                format: 'iife',
            },
            {
                file: 'dist/form-collection.min.js',
                format: 'iife',
                plugins: [terser()]
            }
        ],
        plugins: [typescript()]
    }
]
