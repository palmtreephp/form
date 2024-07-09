import terser from '@rollup/plugin-terser';
import typescript from '@rollup/plugin-typescript';

export default [
    {
        input: 'assets/js/index.ts',
        output: [
            {
                file: 'dist/palmtree-form.pkgd.js',
                format: 'iife',
                //name: 'mostVisible',
            },
            {
                file: 'dist/palmtree-form.pkgd.min.js',
                format: 'iife',
                //name: 'mostVisible',
                plugins: [terser()]
            }
        ],
        plugins: [typescript()] //babel({ babelHelpers: 'bundled' })]
    }
]
