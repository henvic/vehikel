/*global define, require */
/*jshint indent:4 */

define(['AppParams', 'jquery'], function (AppParams, $) {
    "use strict";

    var $postsViewStyleThumbnail = $('#posts-view-style-thumbnail');
    var $postsViewStyleTable = $('#posts-view-style-table');

    var $postsTableView = $("#posts-table-view");
    var $postsThumbnailView = $("#posts-thumbnail-view");

    var changeViewStyle = function (style) {
        if (style === "table") {
            $postsTableView.removeClass("none");
            $postsThumbnailView.addClass("none");
        } else {
            $postsTableView.addClass("none");
            $postsThumbnailView.removeClass("none");
        }

        $.ajax({
            url: "?posts_view_style=" + style,
            type: "HEAD"
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

    var $stockSelect = $("#stock-select");

    $stockSelect.on("change", function (e) {
        var value = e.target.value.split(";");
        var status;
        var make = value[0];
        var model = "";
        var url = AppParams.webroot + "/" + AppParams.postUsername;

        if (! AppParams.status || AppParams.status === "active") {
            status = "";
        } else {
            status = AppParams.status;
        }

        var requestParams = {};

        if (status) {
            requestParams.status = status;
        }

        if (make) {
            requestParams.make = make;
        }

        if (value.length === 2) {
            model = value[1];
            requestParams.model = model;
        }

        var subUrl = $.param(requestParams);

        if (subUrl) {
            url = url + "?" + subUrl;
        }

        window.location = url;
    });

    if (AppParams.accountEditable === true) {
        require(["views/user/stream-manager"], function () {
        });
    }
});
