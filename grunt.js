"use strict";

module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: '<json:package.json>',
    lint: {
      files: ['grunt.js', 'static/javascripts/**/*.js']
    },
    watch: {
      files: ['<config:lint.files>', '<config:recess.dist.src>'],
      tasks: 'default'
    },
    recess: {
      dist: {
        src: ['static/stylesheets/*.less'],
        dest: 'static/stylesheets/style.css',
        options: {
            compile: true,
            compress: true
        }
      }
    },
    jshint: {
      options: {
        curly: true,
        eqeqeq: true,
        immed: true,
        latedef: true,
        newcap: true,
        noarg: true,
        sub: true,
        undef: true,
        boss: true,
        eqnull: true,
        node: true,
        globalstrict: true,
        es5: true
      },
      globals: {
        exports: true
      }
    }
  });

  // Default task.
  grunt.registerTask('default', 'lint recess');

  grunt.loadNpmTasks('grunt-recess');

};