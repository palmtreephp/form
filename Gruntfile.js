module.exports = function (grunt) {
    'use strict';

    grunt.initConfig({

        concat: {
            dist: {
                src: [
                    'node_modules/jquery-bsalert/dist/jquery.bsAlert.js',
                    'src/js/jquery.palmtree-form.js'
                ],
                dest: 'public/js/palmtree-form.js'
            }
        },

        uglify: {
            dist: {
                src: 'public/js/palmtree-form.js',
                dest: 'public/js/palmtree-form.min.js'
            }
        }

    });

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask('default', ['concat', 'uglify']);
};
