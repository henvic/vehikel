/*global define */
/*jshint indent:4 */

define(['AppParams', 'jquery'], function (AppParams, $) {
    "use strict";

    var userStreamPostsElement = $('#user-stream-posts');

    var $postsViewStyleThumbnail = $('#posts-view-style-thumbnail');
    var $postsViewStyleTable = $('#posts-view-style-table');

    var streamCache = {};

    var changeViewStyle = function (style) {
        // at first tries to retrieve from the cache
        if (streamCache[style]) {
            userStreamPostsElement.html(streamCache[style]);
            return;
        }

        var queryParams;

        if (document.location.search !== "") {
            queryParams = document.location.search + "&posts_view_style=" + style;
        } else {
            queryParams = "?posts_view_style=" + style;
        }

        $.ajax({
            url: queryParams,
            success: function (data) {
                streamCache[style] = data;
                userStreamPostsElement.html(data);
            }
        });
    };

    $postsViewStyleTable.on("click", function () {
        changeViewStyle("table");
    });

    $postsViewStyleThumbnail.on("click", function () {
        changeViewStyle("thumbnail");
    });

    var $statusTypeMenuDropdown = $("#status-type-menu-dropdown");

    $statusTypeMenuDropdown.on("click", function (e) {
        if (AppParams.accountEditable === true && e.target.className.match(/type/) !== null) {
            e.preventDefault();
        }
    });
});
