/*global require, AppParams, Modernizr */
/*jshint indent:4 */
var config = (function () {
    "use strict";
    require.config({

        paths: {

            // Core Libraries
            // modernizr shall be called just after CSS, so it is not here
            // @todo find a way to remove the AppParams / foo.js hack
            AppParams: "foo",
            yui: "http://yui.yahooapis.com/3.8.0/build/yui/yui-min",
            jquery: "../vendor/jquery-1.9.1/jquery",
            underscore: "../vendor/underscore-1.3.3/underscore",
            "twitter.bootstrap": "../vendor/bootstrap-2.3.1/js/bootstrap",
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
        // and for the AppParams global variable
        shim: {
            "AppParams" : {
                exports: "AppParams"
            },
            "yui": {
                exports: "YUI"
            },
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

    require(["jquery", "twitter.bootstrap", "jquery.fn.autoResize"],
        function ($) {
            require(["common"], function () {
                if (typeof AppParams.route !== 'undefined') {
                    require(["views/" + AppParams.route], function () {
                    });
                }

                if (typeof AppParams.selfUid !== 'undefined') {
                    require(["authenticated"], function () {
                    });
                }
            });
        });

} ());
