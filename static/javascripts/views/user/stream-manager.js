/*global define, Modernizr */
/*jslint browser: true */

define(['AppParams', 'jquery'], function (AppParams, $) {
    'use strict';

    var $postsThumbnailView = $('#posts-thumbnail-view'),
        $actions = $('.posts-list .actions'),
        $action = $('.posts-list .actions .action');

    if (Modernizr.touch) {
        $postsThumbnailView.addClass('posts-thumbnail-view-touch');
    } else {
        $postsThumbnailView.addClass('posts-thumbnail-view-not-touch');
    }

    $actions.on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $action.on('click', function () {
        var $this = $(this),
            postId = this.parentNode.getAttribute('data-id'),
            newStatus = this.getAttribute('data-status'),
            $postThumbnail = $('#post-id-' + postId + '-thumbnail'),
            $postTableRow = $('#post-id-' + postId + '-row'),
            xhr;

        if ($this.hasClass('disabled')) {
            return;
        }

        xhr = $.ajax({
            url: AppParams.webroot + '/' + AppParams.postUsername + '/' + postId + '/edit',
            type: 'POST',
            dataType: 'json',
            data: {
                hash: AppParams.globalAuthHash,
                status: newStatus
            }
        });

        $postThumbnail.addClass('removing');
        $postTableRow.addClass('removing');

        xhr.done(function () {
            setTimeout(function () {
                $postThumbnail.remove();
                $postTableRow.remove();
            }, 80);
        });

        xhr.fail(function () {
            $postThumbnail.removeClass('removing');
            $postTableRow.removeClass('removing');
        });
    });
});
