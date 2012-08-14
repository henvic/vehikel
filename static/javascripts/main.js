/*global Router, require, uid */
/*jshint indent:4 */
var config = (function () {
    "use strict";
    require.config({

        paths: {

            // Core Libraries
            // modernizr shall be called just after CSS, so it is not here
            jquery: "../vendor/jquery-1.8.0/jquery",
            underscore: "../vendor/underscore-1.3.3/underscore",
            backbone: "../vendor/backbone-0.9.2/backbone",
            "twitter.bootstrap": "../vendor/bootstrap-2.0.4/js/bootstrap",
            "jquery.tablesorter": "../vendor/jquery-tablesorter-2.0.5b/jquery.tablesorter",
            "jquery.fn.autoResize": "../vendor/jquery-fn-autoResize-1.14/jquery.autoresize",

            // Require.js Plugins
            text: "../vendor/require-2.0.4/text-2.0.1",

            // common code
            common: "plugins/common",
            authenticated: "plugins/authenticated"

        },

        // Sets the configuration for your third party scripts that are not AMD compatible
        shim: {
            "underscore": {
                exports: "_"
            },
            "backbone": {
                deps: ["underscore", "jquery"],
                exports: "Backbone"  //attaches "Backbone" to the window object
            },
            "twitter.bootstrap": {
                deps: ["jquery"]
            },
            "jquery.tablesorter": {
                deps: ["jquery"]
            },
            "jquery.fn.autoResize": {
                deps: ["jquery"]
            }


        } // end Shim Configuration

    });

    require(["jquery", "backbone", "routers/router", "twitter.bootstrap", "common", "jquery.fn.autoResize"],
        function ($, Backbone, Router) {
            if (typeof uid !== 'undefined') {
                require(["authenticated"], function () {
                });
            }
        });

} ());

