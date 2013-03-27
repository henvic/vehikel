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
            var $stickAnchor = $("#sticky-anchor");
            var $navbar = $("#navbar");
            var $searchPostsForm = $("#search-posts-form");

            var stickAnchorOffsetTop = $stickAnchor.offset().top;
            var isOnTop = null;

            function stickyRelocate() {
                if (windowObj.scrollTop() > stickAnchorOffsetTop) {
                    if (isOnTop !== false) {
                        isOnTop = false;
                        $navbar.removeClass("navbar-fixed-top-override").addClass("navbar-fixed-top-override2");
                        $searchPostsForm.addClass("search-box-fixed");
                    }
                } else {
                    if (isOnTop !== true) {
                        isOnTop = true;
                        $navbar.addClass("navbar-fixed-top-override").removeClass("navbar-fixed-top-override2");
                        $searchPostsForm.removeClass("search-box-fixed");
                    }
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
