/*global define, require, Galleria */
/*jslint browser: true */

define(['AppParams', 'jquery', 'galleria'], function (AppParams, $) {
    'use strict';

    var $postProductMainInfo = $('#post-product-main-info'),
        $postInfoTabsLinks = $('#post-info-tabs a'),
        $postContact = $('#post-contact'),
        $postPicturesThumbnails = $('#post-pictures-thumbnails'),
        $postPicturesCarousel = $('#post-pictures-carousel'),
        updateGalleria;

    Galleria.loadTheme(AppParams.cdn + 'vendor/galleria-1.2.9/src/themes/classic/galleria.classic.js');

    Galleria.configure({
        dummy: '/images/noimage.jpg',
        transition: 'fade'
    });

    Galleria.on('image', function (e) {
        var gallery = this;

        $(e.imageTarget).unbind('click').click(function () {
            gallery.openLightbox();
        });
    });

    updateGalleria = function () {
        Galleria.run('#galleria', {
            dataSource: AppParams.postGalleryImages
        });
    };

    updateGalleria();

    if (AppParams.accountEditable === true) {
        require(['views/user/post-manager']);
    }

    $postInfoTabsLinks.click(function (e) {
        e.preventDefault();
        $(this).tab('show');

        if (e.target.getAttribute('href') === '#post-contact') {
            $postProductMainInfo.addClass('hidden-phone');
        } else {
            $postProductMainInfo.removeClass('hidden-phone');
        }
    });

    $postContact.on('submit', function (e) {
        var target = $(e.target),
            $submit = $('input[type="submit"]', $postContact);

        if (target.is('form')) {
            e.preventDefault();

            $submit.data('loading-text', 'Enviando...').button('loading');

            $.ajax({
                type: 'POST',
                data: target.serialize(),
                success: function (result) {
                    $postContact.html(result);
                },
                complete: function () {
                    $submit.button('reset');
                }
            });
        }
    });

    $postContact.on('click', function (e) {
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

    $postPicturesCarousel.carousel({
        interval: 0
    });

    $postPicturesThumbnails.on('click', function (e) {
        var $target = $(e.target),
            $closestLi = $target.closest('li');

        if ($closestLi.hasClass('post-picture') && !$target.hasClass('thumbnail-remove-picture')) {
            $postPicturesCarousel.carousel($closestLi.index());
        }
    });

    $postPicturesThumbnails.on('mouseenter', 'li', function (e) {
        e.currentTarget.click();
    });

    // don't let a non-editor user change the checked state of the equipment list
    if (!AppParams.accountEditable) {
        $postProductMainInfo.on('click', '.post-equipments-list [type="checkbox"]', function (e) {
            e.preventDefault();
        });
    }
});
