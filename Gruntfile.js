module.exports = function (grunt) {
    'use strict';

    grunt.initConfig({

        concat: {
            bsAlert: {
                src: 'node_modules/jquery-bsalert/dist/jquery.bsAlert.js',
                dest: 'public/js/jquery.bsAlert.js'
            },
            dist: {
                src: 'src/js/jquery.palmtree-form.js',
                dest: 'public/js/jquery.palmtree-form.js'
            },
            pkgd: {
                src: [
                    'node_modules/jquery-bsalert/dist/jquery.bsAlert.js',
                    'src/js/jquery.palmtree-form.js'
                ],
                dest: 'public/js/palmtree-form.pkgd.js'
            }
        },

        uglify: {
            pkgd: {
                src: 'public/js/palmtree-form.pkgd.js',
                dest: 'public/js/palmtree-form.pkgd.min.js'
            }
        }

    });

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask('default', ['concat', 'uglify']);
};
