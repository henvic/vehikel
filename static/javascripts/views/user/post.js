/*global define, require */
/*jshint indent:4 */

define(['AppParams', 'jquery', 'underscore', 'text!templates/posts/map-modal.html'],
function (AppParams, $, underscore, mapModalTemplate) {
    "use strict";

    if (AppParams.accountEditable === true) {
        require(["views/user/post-manager"], function () {
        });
    }

    var $postProductMainInfo = $("#post-product-main-info");

    var $mapLink = $("#map-link");
    var $mapModal = $("#map-modal");
    var $mapModalInner;

    var loadMapModal = function () {
        var mapLink = $mapLink.attr("href");
        var address = $mapLink.data("address");

        var compiledMapModal = underscore.template(mapModalTemplate);
        var mapModalHtml = compiledMapModal(
            {
                mapLink : mapLink,
                mapIframe : "https://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=" +
                    encodeURIComponent(address) + "&ie=UTF8&z=15&t=m&iwloc=addr&output=embed"
            }
        );

        $mapModal.html(mapModalHtml);

        $mapModalInner = $("#map-modal-inner");

        $mapModalInner.modal({
            "backdrop" : false,
            "show" : false
        });
    };

    $mapLink.on("click", function (e) {
        var displayMapBool = (window.innerWidth >= 608);

        if (! displayMapBool) {
            return;
        }

        e.preventDefault();

        if (! $mapModalInner) {
            loadMapModal();
        }

        $mapModalInner.modal("show");
    });

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
