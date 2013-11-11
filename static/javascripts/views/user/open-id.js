/*global define */
/*jslint browser: true */

define(['AppParams', 'jquery'], function (AppParams, $) {
    'use strict';

    var $setStatusEndButton = $('#set-status-end-button');

    $setStatusEndButton.on('click', function () {
        $setStatusEndButton.attr('disabled', true);

        var xhr = $.ajax({
            url: AppParams.webroot + '/' + AppParams.postUsername + '/' + AppParams.postId + '/edit',
            type: 'POST',
            dataType: 'json',
            data: {
                status: 'end',
                hash: AppParams.globalAuthHash
            }
        });

        xhr.fail(function () {
            $setStatusEndButton.attr('disabled', false);
        });

        xhr.done(function () {
            window.location = AppParams.webroot + '/' + AppParams.postUsername + '/' + AppParams.postId;
        });
    });
});