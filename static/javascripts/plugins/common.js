/*global define, window, global_auth_hash */
/*jshint indent:4 */

define(["jquery", "backbone"], function ($, Backbone) {
    "use strict";

    $(window).ready(function () {
        var NavbarClosure = (function () {
            var windowObj = $(window);
            var stickAnchor = $("#sticky-anchor");
            var navbar = $("#navbar");
            function stickyRelocate() {
                var hasClass = navbar.hasClass("navbar-fixed-top-override");
                if (windowObj.scrollTop() > stickAnchor.offset().top) {
                    if (hasClass) {
                        navbar.removeClass("navbar-fixed-top-override");
                        navbar.addClass("navbar-fixed-top-override2");
                    }
                } else if (! hasClass) {
                    navbar.addClass("navbar-fixed-top-override");
                    navbar.removeClass("navbar-fixed-top-override2");
                }
            }

            $(window).scroll(stickyRelocate);
            stickyRelocate();
        } ());



        var navbarUserClosure = (function () {
            var userTab = $("#navbar-user");
            var openTab = function () {
                userTab.addClass('open');
            };
            var userTabAnchor = $("#navbar-user-button");
            userTabAnchor.click(function (e) {
                window.setTimeout(openTab, 1, true);
                e.preventDefault();
            });
        } ());

        $("a[rel=\"external\"]")
           .click(function () {
                window.open($(this).attr("href"));
                return false;
            });
    });

    return function () {};
});
