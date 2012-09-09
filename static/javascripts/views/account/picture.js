/*global define, _, prompt */
define(['jquery', 'backbone'], function($, Backbone){
    "use strict";

    var accountPictureView = Backbone.View.extend({

        el: 'body',

        // View constructor
        initialize: function() {
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

            var image = $('<img src="" />');

            function previewImage(image) {
                jFileButton.popover({title: 'Preview', content: image, placement: 'bottom'});
                jFileButton.popover('show');

                setTimeout(function () {
                    jFileButton.popover('hide');
                    image.attr('src', '');
                }, 2500);
            }

            function createImage(file) {
                if (! file.type.match('image.*')) {
                    return;
                }

                var reader = new FileReader();

                image.width = 100;
                image.height = 100;

                reader.onload = function (e) {
                    image.attr('src', e.target.result);
                };

                reader.readAsDataURL(file);

                previewImage(image);
            }

        },

        events: {

        }

    });

    // Returns the View class
    return accountPictureView;
});