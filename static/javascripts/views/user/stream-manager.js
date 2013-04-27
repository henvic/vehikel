/*global define */
/*jshint indent:4 */

define(['AppParams', 'jquery'], function (AppParams, $) {
    "use strict";

    //add the "end post" functionality on the user-stream
    var $end = $(".posts-list .end");

    $end.on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        var postId = e.target.getAttribute("data-id");

        var $postThumbnail = $("#post-id-" + postId + "-thumbnail");
        var $postTableRow = $("#post-id-" + postId + "-row");

        $postThumbnail.addClass("remove");

        var xhr = $.ajax({
            url: AppParams.webroot + "/" + AppParams.postUsername + "/" + postId + "/edit",
            type: 'POST',
            dataType: 'json',
            data: {
                hash: AppParams.globalAuthHash,
                status: "end"
            }
        });

        xhr.done(function () {
            $postThumbnail.addClass("removed");
            $postTableRow.remove();
        });

        xhr.fail(function () {
            $postThumbnail.removeClass("remove");
        });
    });
});