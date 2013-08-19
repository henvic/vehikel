/*global define */
/*jshint indent:4 */

define(['AppParams', 'jquery'], function (AppParams, $) {
    "use strict";

    var $actions = $(".posts-list .actions");

    var $action = $(".posts-list .actions .action");

    $actions.on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $action.on("click", function (e) {
        var $this = $(this);

        if ($this.hasClass("disabled")) {
            return;
        }

        var postId = this.parentNode.getAttribute("data-id");
        var newStatus = this.getAttribute("data-status");

        var $postThumbnail = $("#post-id-" + postId + "-thumbnail");
        var $postTableRow = $("#post-id-" + postId + "-row");

        var xhr = $.ajax({
            url: AppParams.webroot + "/" + AppParams.postUsername + "/" + postId + "/edit",
            type: 'POST',
            dataType: 'json',
            data: {
                hash: AppParams.globalAuthHash,
                status: newStatus
            }
        });

        $postThumbnail.addClass("removing");
        $postTableRow.addClass("removing");

        xhr.done(function () {
            setTimeout(function () {
                $postThumbnail.remove();
                $postTableRow.remove();
            }, 80);
        });

        xhr.fail(function () {
            $postThumbnail.removeClass("removing");
            $postTableRow.removeClass("removing");
        });
    });
});