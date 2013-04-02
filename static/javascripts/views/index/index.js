/*global define, require */
/*jshint indent:4 */

define(['AppParams', 'jquery', 'underscore'], function (AppParams, $, underscore) {
    "use strict";

    var $indexContent = $("#index-content");

    var $searchText = $("#search-text");

    var closeIndexContent = function () {
        if ($indexContent === undefined) {
            return;
        }

        $indexContent.stop(true, true)
            .animate({
                height:"toggle",
                opacity:"toggle"
            }, 700);

        setTimeout(function () {
            $indexContent.remove();
            $indexContent = undefined;
        }, 800);
    };

    $searchText.one("keyup", function (e) {
        closeIndexContent();
    });

    $searchText.one("change", function (e) {
        closeIndexContent();
    });

    $searchText.one("paste", function (e) {
        closeIndexContent();
    });
});