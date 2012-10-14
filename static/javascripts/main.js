/*global require, AppParams, Modernizr */
/*jshint indent:4 */
var config = (function () {
    "use strict";
    require.config({

        paths: {

            // Core Libraries
            // modernizr shall be called just after CSS, so it is not here
            jquery: "../vendor/jquery-1.8.0/jquery",
            underscore: "../vendor/underscore-1.3.3/underscore",
            "twitter.bootstrap": "../vendor/bootstrap-2.1.0/js/bootstrap",
            "jquery.tablesorter": "../vendor/jquery-tablesorter-2.0.5b/jquery.tablesorter",
            "jquery.fn.autoResize": "../vendor/jquery-fn-autoResize-1.14/jquery.autoresize",
            "jquery.maskMoney": "../vendor/plentz-jquery-maskmoney-5f9dadd/jquery.maskMoney",

            // Require.js Plugins
            text: "../vendor/require-2.0.5/text-2.0.3",

            // common code
            common: "plugins/common",
            authenticated: "plugins/authenticated"

        },

        // Sets the configuration for your third party scripts that are not AMD compatible
        shim: {
            "underscore": {
                exports: "_"
            },
            "twitter.bootstrap": {
                deps: ["jquery"]
            },
            "jquery.tablesorter": {
                deps: ["jquery"]
            },
            "jquery.fn.autoResize": {
                deps: ["jquery"]
            },
            "jquery.maskMoney": {
                deps: ["jquery"]
            }
        } // end Shim Configuration

    });

    require(["jquery", "twitter.bootstrap", "common", "jquery.fn.autoResize"],
        function ($) {
            if (typeof AppParams.route !== 'undefined') {
                //require...
                require(["views/" + AppParams.route], function () {
                });
            }

            if (typeof AppParams.uid !== 'undefined') {
                require(["authenticated"], function () {
                });
            }
        });

} ());
