/*jshint indent:4 */
module.exports = function (grunt) {
    "use strict";
    // Project configuration.
    grunt.initConfig({
        pkg: "<json:package.json>",
        lint: {
            files: ["grunt.js",
                "static/javascripts/models/**/*.js",
                "static/javascripts/plugins/**/*.js",
                "static/javascripts/routers/**/*.js",
                "static/javascripts/tests/**/*.js",
                "static/javascripts/views/**/*.js",
                "static/javascripts/main.js"
            ]
        },
        watch: {
            files: ["<config:lint.files>", "<config:recess.dist.src>"],
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
                strict: true,
                plusplus: true,
                quotmark: "double",
                trailing: true,
                browser: true,
                es5: true
            },
            globals: {
                exports: true
            }
        }
    });

    // Default task.
    grunt.registerTask("default", "lint recess");

    grunt.loadNpmTasks("grunt-recess");

};
