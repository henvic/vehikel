/*global define */
/*jslint browser: true */

define(['jquery', 'underscore', 'text!templates/posts/map-modal.html', 'AppParams'],
    function ($, underscore, mapModalTemplate, AppParams) {
        'use strict';

        $(window).ready(function () {
            (function () {
                var $openVehicle,
                    $postProductOpenModal,
                    $inputOpenVehicle,
                    $mapLink = $('#map-link'),
                    $mapModal = $('#map-modal'),
                    $mapModalInner,
                    openVehicleButton,
                    isOpenVehicleScreenSaved,
                    loadMapModal;

                openVehicleButton = function () {
                    $postProductOpenModal.modal();

                    $openVehicle.on('submit', function (e) {
                        e.preventDefault();
                        window.location = AppParams.webroot + '/open/' + $inputOpenVehicle.val().toLowerCase();
                    });
                };

                if (window.location.pathname === AppParams.webroot + '/open') {
                    $openVehicle = $('#open-vehicle');
                    $postProductOpenModal = $('#post-product-open-modal');
                    $inputOpenVehicle = $('#input-open-vehicle');

                    $postProductOpenModal.on('shown', function () {
                        $inputOpenVehicle.focus();
                    });

                    openVehicleButton();
                } else {
                    $(document.documentElement).on('keyup', function (e) {
                        if (e.target.nodeName.toLowerCase() !== 'body') {
                            return;
                        }

                        // L
                        if (e.keyCode === 76) {
                            var xhr;

                            e.preventDefault();

                            if (!isOpenVehicleScreenSaved) {
                                xhr = $.ajax({url: AppParams.webroot + '/open'});

                                xhr.done(function (data) {
                                    $('body').append($(data));

                                    $openVehicle = $('#open-vehicle');
                                    $postProductOpenModal = $('#post-product-open-modal');
                                    $inputOpenVehicle = $('#input-open-vehicle');

                                    $postProductOpenModal.on('shown', function () {
                                        $inputOpenVehicle.focus();
                                    });

                                    openVehicleButton();
                                    isOpenVehicleScreenSaved = true;
                                });
                                return;
                            }

                            $postProductOpenModal.modal();
                        }
                    });
                }

                loadMapModal = function () {
                    var mapLink = $mapLink.attr('href'),
                        address = $mapLink.data('address'),
                        compiledMapModal = underscore.template(mapModalTemplate),
                        mapModalHtml = compiledMapModal(
                            {
                                mapLink: mapLink,
                                mapIframe: 'https://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' +
                                    encodeURIComponent(address) + '&ie=UTF8&z=15&t=m&iwloc=addr&output=embed'
                            }
                        );

                    $mapModal.html(mapModalHtml);

                    $mapModalInner = $('#map-modal-inner');

                    $mapModalInner.modal({
                        'backdrop' : false,
                        'show' : false
                    });
                };

                $mapLink.on('click', function (e) {
                    var displayMapBool = (window.innerWidth >= 608);

                    if (!displayMapBool) {
                        return;
                    }

                    e.preventDefault();

                    if (!$mapModalInner) {
                        loadMapModal();
                    }

                    $mapModalInner.modal('show');
                });
            }());

            (function () {
                var tooltip = $('[rel=tooltip]'),
                    likeTooltip = $('[data-rel=tooltip]');

                if (tooltip.length) {
                    tooltip.tooltip();
                }

                if (likeTooltip.length) {
                    likeTooltip.tooltip();
                }
            }());

            var $siteMenu = $('#site-menu');

            //avoid the site menu from closing when clicked
            $siteMenu.on('click', function (e) {
                e.stopPropagation();
            });

            $('a[rel=\"external\"]')
                .click(function () {
                    window.open($(this).attr('href'));
                    return false;
                });
        });
    });
