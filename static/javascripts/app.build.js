({
    dir: "../build/javascripts",
    name: "main",
    useStrict: true,
    optimizeAllPluginResources: true,
    mainConfigFile: "main.js",
    preserveLicenseComments: false,
    paths: {

        // Core Libraries
        // modernizr shall be called just after CSS, so it is not here
        jquery: "../vendor/jquery-1.8.0/jquery",
        underscore: "../vendor/underscore-1.3.3/underscore",
        "twitter.bootstrap": "../vendor/bootstrap-2.1.0/js/bootstrap",
        "jquery.tablesorter": "../vendor/jquery-tablesorter-2.0.5b/jquery.tablesorter",
        "jquery.fn.autoResize": "../vendor/jquery-fn-autoResize-1.14/jquery.autoresize",

        // Require.js Plugins
        text: "../vendor/require-2.0.5/text-2.0.3",

        // common code
        common: "plugins/common",
        authenticated: "plugins/authenticated"

    }
})