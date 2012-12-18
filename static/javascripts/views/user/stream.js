/*global define */
define(['jquery'], function($) {
    "use strict";

    var userStreamPostsElement = $('#user-stream-posts');

    var postsViewStyleThumbnailRadio = $('#posts_view_style_thumbnail');
    var postsViewStyleTableRadio = $('#posts_view_style_table');

    var streamCache = {};

    var changeViewStyle = function(style) {
        // at first tries to retrieve from the cache
        if (streamCache[style]) {
            userStreamPostsElement.html(streamCache[style]);
            return;
        }

        $.ajax({
            url: '?posts_view_style=' + style,
            success: function(data) {
                streamCache[style] = data;
                userStreamPostsElement.html(data);
            }
        });
    };

    postsViewStyleTableRadio.on("click", function() {
        changeViewStyle("table");
    });

    postsViewStyleThumbnailRadio.on("click", function() {
        changeViewStyle("thumbnail");
    });

    var $statusTypeMenuDropdown = $("#status-type-menu-dropdown");

    $statusTypeMenuDropdown.on("click", function (e) {
        if (AppParams.accountEditable === true && e.target.className.match(/type/) !== null) {
            e.preventDefault();
        }
    });
});
