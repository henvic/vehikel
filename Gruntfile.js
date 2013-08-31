/*global module */

module.exports = function (grunt) {
    "use strict";
    // Project configuration.
    grunt.initConfig({
        pkg: "<json:package.json>",
        watch: {
            files: ["<config:recess.dist.src>"],
            tasks: "default"
        },
        recess: {
            dist: {
                src: ["static/stylesheets/*.less"],
                dest: "static/stylesheets/style.css",
                options: {
                    compile: false,
                    compress: false
                }
            }
        },
        jslint: {
            all: {
                src: ["grunt.js",
                    "application/search/**/*.js",
                    "static/javascripts/models/**/*.js",
                    "static/javascripts/plugins/**/*.js",
                    "static/javascripts/routers/**/*.js",
                    "static/javascripts/tests/**/*.js",
                    "static/javascripts/views/**/*.js",
                    "static/javascripts/main.js"
                    ],
                options: {
                    shebang: true
                }
            }
        }
    });

    // Default task.
    grunt.registerTask("default", ["jslint", "recess"]);
    grunt.registerTask("lint", ["jslint"]);

    grunt.loadNpmTasks("grunt-jslint");
    grunt.loadNpmTasks("grunt-recess");

};
