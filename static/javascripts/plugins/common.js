/*global define, window */
/*jshint indent:4 */

define(["jquery", "twitter.bootstrap", "jquery.fn.autoResize"], function ($) {
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

        $("a[rel=\"external\"]")
           .click(function () {
                window.open($(this).attr("href"));
                return false;
            });
    });

    return function () {};
});
