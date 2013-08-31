/*global define, require */
/*jslint browser: true */

define(['AppParams', 'jquery'], function (AppParams, $) {
    'use strict';

    var $postsViewStyleThumbnail = $('#posts-view-style-thumbnail'),
        $postsViewStyleTable = $('#posts-view-style-table'),
        $postsTableView = $('#posts-table-view'),
        $postsThumbnailView = $('#posts-thumbnail-view'),
        $statusTypeMenuDropdown = $('#status-type-menu-dropdown'),
        $stockSelect = $('#stock-select'),
        changeViewStyle;

    changeViewStyle = function (style) {
        if (style === 'table') {
            $postsTableView.removeClass('none');
            $postsThumbnailView.addClass('none');
        } else {
            $postsTableView.addClass('none');
            $postsThumbnailView.removeClass('none');
        }

        $.ajax({
            url: '?posts_view_style=' + style,
            type: 'HEAD'
        });
    };

    $postsViewStyleTable.on('click', function () {
        changeViewStyle('table');
    });

    $postsViewStyleThumbnail.on('click', function () {
        changeViewStyle('thumbnail');
    });

    $statusTypeMenuDropdown.on('click', function (e) {
        if (AppParams.accountEditable === true && e.target.className.match(/type/) !== null) {
            e.preventDefault();
        }
    });

    $stockSelect.on('change', function (e) {
        var value = e.target.value.split(';'),
            status,
            make = value[0],
            model = '',
            url = AppParams.webroot + '/' + AppParams.postUsername,
            requestParams = {},
            subUrl;

        if (!AppParams.status || AppParams.status === 'active') {
            status = '';
        } else {
            status = AppParams.status;
        }

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

        subUrl = $.param(requestParams);

        if (subUrl) {
            url = url + '?' + subUrl;
        }

        window.location = url;
    });

    if (AppParams.accountEditable === true) {
        require(['views/user/stream-manager']);
    }
});
