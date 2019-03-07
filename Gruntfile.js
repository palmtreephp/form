module.exports = function (grunt) {
    'use strict';

    grunt.initConfig({
        concat: {
            bsAlert: {
                src: 'node_modules/jquery-bsalert/dist/jquery.bsAlert.js',
                dest: 'public/js/jquery.bsAlert.js'
            },
            pkgd: {
                src: [
                    'node_modules/jquery-bsalert/dist/jquery.bsAlert.js',
                    'public/js/jquery.palmtree-custom-file.js',
                    'public/js/jquery.palmtree-recaptcha.js',
                    'public/js/jquery.palmtree-form-collection.js',
                    'public/js/jquery.palmtree-form.js'
                ],
                dest: 'public/dist/js/palmtree-form.pkgd.js'
            }
        },

        uglify: {
            pkgd: {
                src: 'public/dist/js/palmtree-form.pkgd.js',
                dest: 'public/dist/js/palmtree-form.pkgd.min.js'
            }
        }

    });

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask('default', ['concat', 'uglify']);
};
