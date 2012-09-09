/*global define */
/*jshint indent:4 */
define(['jquery', 'backbone', 'views/view', 'views/anotherView'], function ($, Backbone, MainView, AnotherView) {
    "use strict";

    var Router = Backbone.Router.extend({
        firstPageRoute: true,
        /**
         * Tells if the routes have already been applied at least once
         * @return {Boolean}
         */
        firstPageLoad: function () {
            if (this.firstPageRoute === true) {
                this.firstPageRoute = false;
                return true;
            }

            return this.firstPageRoute;
        },
        initialize: function () {
            Backbone.history.start({pushState: true, root: "/"});
        },
        routes: {
            '': 'home',
            'account/picture' : 'account/picture',
            '*default': 'default'

        },
        'default': function (path) {
            if (! this.firstPageLoad()) {
                return;
            }
        },
        'home': function () {
            if (! this.firstPageLoad()) {
                return;
            }
        },
        'account/picture': function () {
            if (! this.firstPageLoad()) {
                return;
            }

            require(['views/account/picture'], function (AccountPictureView) {
                var accountPictureView = new AccountPictureView();
            });
        }
    });

    new Router();
});