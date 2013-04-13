/*global define, require, Galleria */
/*jshint indent:4 */

define(['AppParams', 'jquery', 'galleria'],
function (AppParams, $) {
    "use strict";

    Galleria.loadTheme(AppParams.cdn + 'vendor/galleria-1.2.9/src/themes/classic/galleria.classic.js');

    Galleria.configure({
        dummy: '/images/noimage.jpg',
        transition: 'fade'
    });

    Galleria.on("image", function (e) {
        var gallery = this;

        $(e.imageTarget).unbind("click").click(function() {
            gallery.toggleFullscreen();
        });
    });

    var updateGalleria = function () {
        Galleria.run("#galleria", {
            dataSource: AppParams.postGalleryImages
        });
    };

    updateGalleria();

    if (AppParams.accountEditable === true) {
        require(["views/user/post-manager"], function () {
        });
    }

    var $postProductMainInfo = $("#post-product-main-info");
    var $postInfoTabsLinks = $('#post-info-tabs a');

    $postInfoTabsLinks.click(function (e) {
        e.preventDefault();
        $(this).tab('show');

        if (e.target.getAttribute("href") === "#post-contact") {
            $postProductMainInfo.addClass("hidden-phone");
        } else {
            $postProductMainInfo.removeClass("hidden-phone");
        }
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

    $postPicturesThumbnails.on("mouseenter", "li", function (e) {
        e.currentTarget.click();
    });

    // don't let a non-editor user change the checked state of the equipment list
    if (! AppParams.accountEditable) {
        $postProductMainInfo.on("click", '.post-equipments-list [type="checkbox"]', function (e) {
            e.preventDefault();
        });
    }
});
