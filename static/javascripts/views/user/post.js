/*global define */
/*jshint indent:4 */

define(['jquery'], function ($) {
    "use strict";

    if (AppParams.postEditable === true) {
        require(["views/user/post-manager"], function () {
        });
    }

    var $postInfoTabsLinks = $('#post-info-tabs a');

    $postInfoTabsLinks.click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    var $postContact = $('#post-contact');

    $postContact.on("submit", function (e) {
        var target = $(e.target);
        if (target.is('form')) {
            e.preventDefault();

            var $submit = $('input[type="submit"]', $postContact);

            $submit.data('loading-text', 'Enviando...').button('loading');

            $.ajax({
                type: 'POST',
                data: target.serialize(),
                success: function (result) {
                    $postContact.html(result);
                },
                complete: function (result) {
                    $submit.button('reset');
                }
            });
        }
    });

    $postContact.on("click", function (e) {
        var target = $(e.target);
        if (target.is('.close')) {
            $.ajax({
                type: 'GET',
                success: function (result) {
                    $postContact.html(result);
                }
            });
        }
    });

    var $postPicturesThumbnails = $("#post-pictures-thumbnails");
    var $postPicturesCarousel = $("#post-pictures-carousel");

    $postPicturesCarousel.carousel({
        interval: 0
    });

    $postPicturesThumbnails.on("click", function (e) {
        var $target = $(e.target);
        var $closestLi = $target.closest("li");

        if ($closestLi.hasClass("post-picture") && ! $target.hasClass("thumbnail-remove-picture")) {
            $postPicturesCarousel.carousel($closestLi.index());
        }
    });
});
