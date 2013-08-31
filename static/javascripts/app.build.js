({
    dir: '../build/javascripts',
    name: 'main',
    useStrict: true,
    optimizeAllPluginResources: true,
    mainConfigFile: 'main.js',
    preserveLicenseComments: false,
    paths: {

        // Core Libraries
        // modernizr shall be called just after CSS, so it is not here
        // @todo find a way to remove the AppParams / foo.js hack
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
        authenticated: 'plugins/authenticated'

    }
});
