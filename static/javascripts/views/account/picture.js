/*global define */
define(['jquery'], function($) {
    "use strict";

    //jQuery doesn't copy e.dataTransfer natively
    var fileButton = document.getElementById('choose-image');
    var jFileButton = $('#choose-image');

    var fileInput = document.getElementById("upload-image");

    $(fileInput).hide();

    fileInput.addEventListener("change", function (e) {
        createImage(this.files[0]);
    }, false);

    fileButton.addEventListener("click", function (e) {
        $(fileInput).show().focus().click().hide();
        e.preventDefault();
    }, false);

    fileButton.addEventListener("dragenter", function (e) {
        e.stopPropagation();
        e.preventDefault();
    }, false);

    fileButton.addEventListener("dragover", function (e) {
        e.stopPropagation();
        e.preventDefault();
    }, false);

    fileButton.addEventListener("drop", function (e) {
        e.stopPropagation();
        e.preventDefault();

        createImage(e.dataTransfer.files[0]);
    }, false);

    var imageElement = document.createElement("img");

    function previewImage(imageElement) {
        jFileButton.popover({
            title: 'Preview',
            content: imageElement,
            html: true,
            placement: 'bottom'
        });
        jFileButton.popover('show');
        console.log(imageElement);

        setTimeout(function () {
            jFileButton.popover('hide');
            imageElement.src = "";
        }, 2500);
    }

    function createImage(file) {
        if (! file.type.match('image.*')) {
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
    }
});
