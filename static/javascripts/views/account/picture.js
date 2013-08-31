/*global define, FileReader */
/*jslint browser: true */

define(['jquery'], function ($) {
    'use strict';

    //jQuery doesn't copy e.dataTransfer natively
    var fileButton = document.getElementById('choose-image'),
        jFileButton = $('#choose-image'),
        fileInput = document.getElementById('upload-image'),
        imageElement = document.createElement('img'),
        createImage,
        previewImage;

    $(fileInput).hide();

    fileInput.addEventListener('change', function () {
        createImage(this.files[0]);
    }, false);

    fileButton.addEventListener('click', function (e) {
        $(fileInput).show().focus().click().hide();
        e.preventDefault();
    }, false);

    fileButton.addEventListener('dragenter', function (e) {
        e.stopPropagation();
        e.preventDefault();
    }, false);

    fileButton.addEventListener('dragover', function (e) {
        e.stopPropagation();
        e.preventDefault();
    }, false);

    fileButton.addEventListener('drop', function (e) {
        e.stopPropagation();
        e.preventDefault();

        createImage(e.dataTransfer.files[0]);
    }, false);

    previewImage = function (imageElement) {
        jFileButton.popover({
            title: 'Preview',
            content: imageElement,
            html: true,
            placement: 'bottom'
        });
        jFileButton.popover('show');

        setTimeout(function () {
            jFileButton.popover('hide');
            imageElement.src = '';
        }, 2500);
    };

    createImage = function (file) {
        if (typeof FileReader !== 'function') {
            return;
        }

        if (!file.type.match('image.*')) {
            return;
        }

        var reader = new FileReader();

        imageElement.width = 100;
        imageElement.height = 100;

        reader.onload = function (e) {
            imageElement.src = e.target.result;
            previewImage(imageElement);
        };

        reader.readAsDataURL(file);
    };
});
