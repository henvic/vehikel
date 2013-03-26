/*global define, window */
/*jshint indent:4 */

define(["AppParams", "jquery"],
function (AppParams, $) {
    "use strict";

    $(window).ready(function () {
        var tooltipClosure = (function () {
            var tooltip = $("[rel=tooltip]");
            var likeTooltip = $("[data-rel=tooltip]");

            if (tooltip.length) {
                tooltip.tooltip();
            }

            if (likeTooltip.length) {
                likeTooltip.tooltip();
            }
        } ());

        var NavbarClosure = (function () {
            var windowObj = $(window);
            var stickAnchor = $("#sticky-anchor");
            var navbar = $("#navbar");
            function stickyRelocate() {
                var hasClass = navbar.hasClass("navbar-fixed-top-override");
                if (windowObj.scrollTop() > stickAnchor.offset().top) {
                    if (hasClass) {
                        navbar.removeClass("navbar-fixed-top-override").addClass("navbar-fixed-top-override2");
                    }
                } else if (! hasClass) {
                    navbar.addClass("navbar-fixed-top-override").removeClass("navbar-fixed-top-override2");
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
