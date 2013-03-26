/*global define, window */
/*jshint indent:4 */

define(["AppParams", "jquery", "underscore", "text!templates/search/form.html"],
    function (AppParams, $, underscore, searchFormTemplate) {
        "use strict";

        var $searchResults = $("#search-results");

        $searchResults.on("click", ".posts-table-view tr", function (e) {
            var link = e.currentTarget.getAttribute("data-link");

            if (e.target.tagName.toLowerCase() !== "a") {
                e.preventDefault();
                window.location = link;
            }
        });

        var $searchBox = $("#search-box");

        $searchBox.html(searchFormTemplate);

        return function () {};
    });
