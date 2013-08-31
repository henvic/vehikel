/*global require, AppParams, Modernizr */
/*jslint browser: true */
var config = (function () {
    'use strict';
    require.config({

        paths: {

            // Core Libraries
            // modernizr shall be called just after CSS, so it is not here
            // The AppParams / foo.js hack is just so it is possible to call AppParams as a module
            AppParams: 'foo',
            yui: 'http://yui.yahooapis.com/3.9.1/build/yui/yui-min',
            jquery: '../vendor/jquery-1.9.1/jquery',
            underscore: '../vendor/underscore-1.4.4/underscore',
            'twitter.bootstrap': '../vendor/bootstrap-2.3.1/js/bootstrap',
            'jcrop': '../vendor/Jcrop-0.9.12/js/jquery.JCrop',
            'jquery.tablesorter': '../vendor/jquery-tablesorter-2.0.5b/jquery.tablesorter',
            'jquery.fn.autoResize': '../vendor/jquery-fn-autoResize-1.14/jquery.autoresize',
            'jquery.maskMoney': '../vendor/plentz-jquery-maskmoney-5f9dadd/jquery.maskMoney',
            'ckeditor' : '../vendor/ckeditor-4.1/ckeditor',
            'galleria' : '../vendor/galleria-1.2.9/src/galleria',

            // Require.js Plugins
            text: '../vendor/require-2.0.5/text-2.0.3',

            // common code
            common: 'plugins/common',
            search: 'plugins/search',
            authenticated: 'plugins/authenticated'

        },

        // Sets the configuration for your third party scripts that are not AMD compatible
        // and for the AppParams global variable
        shim: {
            'AppParams' : {
                exports: 'AppParams'
            },
            'yui': {
                exports: 'YUI'
            },
            'underscore': {
                exports: '_'
            },
            'twitter.bootstrap': {
                deps: ['jquery']
            },
            'jcrop' : {
                deps: ['jquery']
            },
            'jquery.tablesorter': {
                deps: ['jquery']
            },
            'jquery.fn.autoResize': {
                deps: ['jquery']
            },
            'jquery.maskMoney': {
                deps: ['jquery']
            },
            'ckeditor' : {
            },
            'galleria': {
                deps: ['jquery']
            }
        } // end Shim Configuration

    });

    require(['jquery', 'twitter.bootstrap', 'jquery.fn.autoResize'],
        function () {
            if (window.location.pathname !== AppParams.webroot + '/search') {
                require(['plugins/facets']);
            }

            require(['common', 'search'], function () {
                if (AppParams.route !== undefined) {
                    require(['views/' + AppParams.route]);
                }

                if (AppParams.selfUid !== undefined) {
                    require(['authenticated']);
                }
            });
        });

}());
