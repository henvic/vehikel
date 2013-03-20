/*global define */
/*jshint indent:4 */

define(['AppParams', 'jquery', 'yui', 'underscore', 'text!templates/help/html.html', 'jquery.maskMoney' ],
    function (AppParams, $, YUI, underscore, htmlTemplate) {
        "use strict";

        var addPictureTemplate = '<li class="span1 post-upload post-upload-free-slot">' +
            '<img src="' + AppParams.cdn + 'images/post-upload-image.png" alt="Upload de imagem" />' +
            '</li>';

        var $postPicturesThumbnails = $("#post-pictures-thumbnails");
        var $postPicturesCarousel = $("#post-pictures-carousel");
        var $postPicturesCarouselInner = $("#post-pictures-carousel-inner");

        var removePicture = function (pictureId) {
            var $pictureThumbnail = $("#post-thumbnail-picture-id-" + pictureId);
            var $picture = $("#post-picture-id-" + pictureId);
            $picture.addClass("picture-disabled");
            $pictureThumbnail.addClass("picture-disabled");
            $.ajax({
                url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId + "/picture/delete",
                type: 'POST',
                dataType: 'json',
                data: ({
                    hash: AppParams.globalAuthHash,
                    picture_id: pictureId
                }),
                success: function (data, textStatus, jqXHR) {
                    $pictureThumbnail.remove();
                    $postPicturesThumbnails.append(addPictureTemplate);
                    if ("post-picture-id-" + pictureId === $(".active", $postPicturesCarousel).attr("id")) {
                        $postPicturesCarousel.carousel('next');
                        setTimeout(function () {
                            $picture.remove();
                        }, 1200);
                    } else {
                        $picture.remove();
                    }
                },
                failure: function () {
                    $picture.removeClass("picture-disabled");
                    $pictureThumbnail.removeClass("picture-disabled");
                }
            });
        };

        YUI({filter: "raw"}).use("uploader", function (Y) {
            var uploadImage = function () {
                var uploader = new Y.Uploader({
                    uploadURL: AppParams.webroot + "/" + AppParams.postUsername + "/" +
                        AppParams.postId + "/picture/add",
                    postVarsPerFile: {hash: AppParams.globalAuthHash},
                    fileFilters: {
                        description: "Images",
                        extensions: "*.jpg;*.jpeg;*.gif;*.png"
                    }
                });
                var foo = document.createElement("div");
                uploader.render(foo);
                uploader.openFileSelectDialog();

                uploader.after("fileselect", function (files) {
                    Y.each(files.fileList, function (fileInstance) {
                        var $slot = $(".post-upload-free-slot").first();

                        $slot.before('<li class="span1 post-upload" id="' +
                            "post-upload-file-" + fileInstance.get("id") +
                            '"><img src="' + AppParams.cdn +
                            '/images/post-upload-image-sending.png" alt="Upload de imagem" />' +
                            '<span class="thumbnail-info-picture-top-large">0%</span></li>');

                        $slot.remove();
                    });
                    uploader.uploadAll();
                });

                uploader.on("uploadprogress", function (event) {
                    var $uploadPostPicture = $("#post-upload-file-" + event.file.get("id"));

                    $uploadPostPicture.children(".thumbnail-info-picture-top-large").text(event.percentLoaded + "%");
                });

                uploader.on("uploaderror", function (event) {
                    var $uploadPostPicture = $("#post-upload-file-" + event.file.get("id"));

                    var feedback = '<img src="' + AppParams.cdn + '/images/post-upload-image-error.png" alt="Erro" />';

                    $uploadPostPicture.removeClass("thumbnail-info-picture-top-large");
                    $uploadPostPicture.addClass("post-upload-error");
                    $uploadPostPicture.html(feedback);
                });

                uploader.on("uploadcomplete", function (event) {
                    var $picturePlaceholder = $("#post-picture-placeholder");

                    var data = $.parseJSON(event.data);

                    var imgSquare = AppParams.imagesCdn + data.id + '-' + data.secret + '-square.jpg';
                    var imgMedium = AppParams.imagesCdn + data.id + '-' + data.secret + '-medium.jpg';
                    var imgLarge = AppParams.imagesCdn + data.id + '-' + data.secret + '-large.jpg';

                    if ($picturePlaceholder.length !== 0) {
                        $picturePlaceholder.remove();
                    }

                    var $uploadPostPicture = $("#post-upload-file-" + event.file.get("id"));

                    var thumbnailPicture = '<li class="span1 post-picture post-picture-move"' +
                        ' id="post-thumbnail-picture-id-' +
                        data.id + '" ' + 'data-picture-id="' + data.id + '">' +
                        '<img src="' + imgSquare + '" alt="picture" />' +
                        '<small class="thumbnail-remove-picture thumbnail-info-picture">×</small></li>';

                    $uploadPostPicture.before(thumbnailPicture);
                    $uploadPostPicture.remove();

                    var picture = '<div class="item" id="post-picture-id-' + data.id + '">' +
                        '<a href="' + imgLarge + '" rel="external">' +
                        '<img src="' + imgMedium + '" alt="picture" />' +
                        '</a></div>';

                    $postPicturesCarouselInner.append(picture);
                    var $picture = $("#post-picture-id-" + data.id);
                    var picturePosition = $picture.index();

                    if (picturePosition === 0) {
                        $picture.addClass("active");
                    } else {
                        $postPicturesCarousel.carousel(picturePosition);
                    }
                    uploader.destroy();
                });
            };

            $postPicturesThumbnails.on("click", function (e) {
                var $target = $(e.target);
                var $closestLi = $target.closest("li");

                if ($closestLi.hasClass("post-upload-error")) {
                    $closestLi.parent().append(addPictureTemplate);
                    $closestLi.remove();
                } else if ($target.hasClass("thumbnail-remove-picture")) {
                    var pictureId = $closestLi.data("picture-id");
                    removePicture(pictureId);
                } else if ($closestLi.hasClass("post-upload-free-slot")) {
                    uploadImage();
                }
            });

        });

        YUI().use('dd-constrain', 'dd-proxy', 'dd-drop', 'dd-delegate', 'dd-drop-plugin', function (Y) {
            Y.DD.DDM.on('drop:over', function (e) {
                var drag = e.drag.get('node'),
                    drop = e.drop.get('node');

                if (drop.get('tagName').toLowerCase() === 'li') {
                    if (!goingUp) {
                        drop = drop.get('nextSibling');
                    }
                    e.drop.get('node').get('parentNode').insertBefore(drag, drop);
                    //Resize this nodes shim, so we can drop on it later.
                    e.drop.sizeShim();
                }
            });

            Y.DD.DDM.on('drag:drag', function (e) {
                //Get the last y point
                var y = e.target.lastXY[1];
                //is it greater than the lastY var?
                if (y < lastY) {
                    //We are going up
                    goingUp = true;
                } else {
                    //We are going down.
                    goingUp = false;
                }
                //Cache for next check
                lastY = y;
            });

            Y.DD.DDM.on('drag:start', function (e) {
                var drag = e.target;
                drag.get('node').setStyle('opacity', '.25');
                drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
                drag.get('dragNode').setStyles({
                    opacity: '.5',
                    borderColor: drag.get('node').getStyle('borderColor'),
                    backgroundColor: drag.get('node').getStyle('backgroundColor')
                });
            });

            Y.DD.DDM.on('drag:end', function (e) {
                var drag = e.target;
                drag.get('node').setStyles({
                    visibility: '',
                    opacity: '1'
                });

                var orderLis = Y.Node.all('#post-pictures-thumbnails .post-picture');
                var ids = [];
                orderLis.each(function (v, k) {
                    ids.push(v.getData("picture-id"));
                });

                var idsAmount = ids.length;
                for (var idPosition = 0; idPosition < idsAmount; idPosition += 1) {
                    var $pictureElement = $('#post-picture-id-' + ids[idPosition]);
                    $pictureElement.detach();
                    $postPicturesCarouselInner.append($pictureElement);
                }

                // @todo add success and failure handling
                $.ajax({
                    url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId + "/picture/sort",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        hash: AppParams.globalAuthHash,
                        picture_id: ids
                    }
                });
            });

            Y.DD.DDM.on('drag:drophit', function (e) {
                var drop = e.drop.get('node');
                var drag = e.drag.get('node');

                //if we are not on an li, we must have been dropped on a ul
                if (drop.get('tagName').toLowerCase() !== 'li') {
                    if (!drop.contains(drag)) {
                        drop.appendChild(drag);
                    }
                }
            });

            var goingUp = false, lastY = 0;

            var del = new Y.DD.Delegate({
                container: '#post-pictures-thumbnails',
                nodes: '.post-picture',
                target: {
                    padding: '0 0 0 20'
                }
            });

            del.dd.plug(Y.Plugin.DDProxy, {
                moveOnEnd: false,
                cloneNode: true
            });

            del.dd.plug(Y.Plugin.DDConstrained, {
                constrain2node: '#post-pictures-thumbnails'
            });

            del.dd.plug(Y.Plugin.Drop);
        });

        var $htmlFormattingPopover = $('.html-formatting-popover');
        $htmlFormattingPopover.popover({title: 'HTML', content: htmlTemplate, placement: 'top'});

        var confirmBeforeExit = false;

        function doConfirmBeforeExit()
        {
            var response;

            if (confirmBeforeExit) {
                response = "Você tem mudanças que ainda não foram salvas";
            } else {
                response = null;
            }

            return response;
        }

        window.onbeforeunload = doConfirmBeforeExit;

        document.onkeyup = function (e) {
            // on pressing the esc key
            if (e.keyCode === 27 &&
                e.target.id === "post-description-text-edit" &&
                ! confirmBeforeExit) {
                cancelDescriptionEdit();
            }
        };

        var $postProductType = $('#post-product-type');
        var $postProductMake = $('#post-product-make');
        var $postProductModel = $('#post-product-model');
        var $postProductEngine = $('#post-product-engine');

        var $postProductName = $('#post-product-name');
        var $postProductNameEdit = $('#post-product-name-edit');
        var $postProductNameEditingArea = $('#post-product-name-editing-area');
        var $postProductNameCancel = $('#post-product-name-cancel');

        var $postProductInfo = $('#post-product-info');
        var $postProductInfoEditingArea = $('#post-product-info-editing-area');
        var $postProductMainInfo = $("#post-product-main-info");
        var $postProductInfoOthers = $("#post-product-info-others");

        var $postDescriptionText = $('#post-description-text');
        var $postDescriptionTextEdit = $('#post-description-text-edit');
        var $postDescriptionEditingArea = $('#post-description-editing-area');
        var $postDescriptionTextSave = $('#post-description-text-save');
        var $postDescriptionTextCancel = $('#post-description-text-cancel');

        var $postStatus = $('#post-status');

        var postProductTypeValue = $postProductType.val();
        var postProductMakeValue = $postProductMake.val();
        var postProductModelValue = $postProductModel.val();
        var postProductNameEditValue = $postProductNameEdit.val();
        var postProductInfoEditingAreaOriginal = $postProductInfoEditingArea.html();

        var $editPostButton = $("#edit-post-button");

        var $postStatusStaging = $("#post-status-staging");
        var $postStatusEnd = $("#post-status-end");
        var $postStatusInfoStaging = $("#post-status-info-staging");

        $editPostButton.on("click", function (e) {
            openPostProductNameEdit();
            openPostProductInfoEdit();
            openDescriptionEdit();
            $(window).scrollTop($postProductNameEditingArea.position().top);
        });

        var maskMoney = function ($element) {
            $element.maskMoney(
                {
                    symbol: 'R$',
                    thousands: '.',
                    decimal: ',',
                    defaultZero: false
                }
            );
        };

        var loadPostProductInfoEditingAreaElements = function () {
            maskMoney($('#post-product-info-editing-area #price'));
        };

        loadPostProductInfoEditingAreaElements();

        var loadPostProductMakes = function (type, make) {
            $.ajax({
                url: AppParams.webroot + '/typeahead',
                type: 'GET',
                dataType: 'json',
                data: ({
                    search: "makes",
                    type: type
                }),
                success: function (data, textStatus, jqXHR) {
                    if (data.values instanceof Array) {
                        var $optGroup = $($postProductMake.find("optgroup")[0]);
                        var $entrySet = $("<select>");
                        $entrySet.append('<option value="">-</option>');
                        $.each(data.values, function (key, value) {
                            $entrySet.append($('<option>', { value : value }).text(value));
                        });

                        $entrySet.append($('<option>', { 'data-action' : 'other' }).text("Outro"));

                        $postProductMake.removeAttr("disabled");
                        $optGroup.html($entrySet.children());

                        if (make !== undefined) {
                            $postProductMake.val(make);

                            if ($postProductMake.val() === make) {
                                return;
                            }

                            $optGroup.append($('<option>', { value : make }).text(make));
                            $postProductMake.val(make);
                        }
                    }
                }
            });
        };

        $postProductType.on("change", function (e) {
            $postProductMake.val("");
            $postProductMake.attr("disabled", "disabled");
            $postProductModel.val("");
            $postProductModel.attr("disabled", "disabled");
            loadPostProductMakes($postProductType.val());
        });

        var setCustomMake = function () {
            var make = window.prompt("Marca?");

            if (! make) {
                return;
            }

            $postProductMake.val(make);
            if ($postProductMake.val() === make) {
                loadPostProductModels($postProductType.val(), $postProductMake.val());
                return;
            }

            var $optGroup = $($postProductMake.find("optgroup")[0]);
            var $entrySet = $("<select>");

            $entrySet.append($('<option>', { value : make }).text(make));

            $optGroup.append($entrySet.children());

            $postProductMake.val(make);

            $postProductModel.removeAttr("disabled", "disabled");
        };

        var setCustomModel = function () {
            var model = window.prompt("Modelo?");

            if (! model) {
                return;
            }

            $postProductModel.val(model);
            if ($postProductModel.val() === model) {
                return;
            }

            var $optGroup = $($postProductModel.find("optgroup")[0]);
            var $entrySet = $("<select>");

            $entrySet.append($('<option>', { value : model }).text(model));

            $optGroup.append($entrySet.children());

            $postProductModel.val(model);
        };

        var loadPostProductModels = function (type, make, model) {
            $.ajax({
                url: AppParams.webroot + '/typeahead',
                type: 'GET',
                dataType: 'json',
                data: ({
                    search: "models",
                    type: type,
                    make: make
                }),
                success: function (data, textStatus, jqXHR) {
                    if (data.values instanceof Array) {
                        $postProductModel.html('<optgroup label="Modelo"><option value="">-</option></optgroup>');
                        var $optGroup = $($postProductModel.find("optgroup")[0]);
                        var $entrySet = $("<select>");
                        $.each(data.values, function (key, value) {
                            $entrySet.append($('<option>', { value : value }).text(value));
                        });

                        $entrySet.append($('<option>', { 'data-action' : 'other' }).text("Outro"));

                        $postProductModel.removeAttr("disabled");
                        $optGroup.append($entrySet.children());

                        if (model !== undefined) {
                            $postProductModel.val(model);

                            if ($postProductModel.val() === model) {
                                return;
                            }

                            $optGroup.append($('<option>', { value : model }).text(model));
                            $postProductModel.val(model);
                        }
                    }
                }
            });
        };

        loadPostProductMakes($postProductType.val(), $postProductMake.val());
        loadPostProductModels($postProductType.val(), $postProductMake.val(), $postProductModel.val());

        $postProductMake.on("change", function (e) {
            $postProductModel.val("");
            $postProductModel.attr("disabled", "disabled");

            if ($(':selected', $postProductMake).data("action") === 'other') {
                setCustomMake();
            } else {
                loadPostProductModels($postProductType.val(), $postProductMake.val());
            }
        });

        $postProductModel.on("change", function (e) {
            if ($(':selected', $postProductModel).data("action") === 'other') {
                setCustomModel();
            }
        });

        var openPostProductNameEdit = function () {
            $postProductName.attr('unselectable', 'on').on('selectstart', false);
            $postProductName.addClass("hidden");
            $postProductMake.focus();

            $postProductNameEdit.removeClass("hidden");
            $postProductNameEditingArea.removeClass("hidden");
        };

        var closePostProductNameEdit = function () {
            $postProductName.removeClass("hidden");
            $postProductNameEditingArea.addClass("hidden");
        };

        $postProductName.on("click", function (e) {
            openPostProductNameEdit();
        });

        $postProductNameCancel.on("click", function (e) {
            closePostProductNameEdit();
            $postProductType.val(postProductTypeValue);
            loadPostProductMakes(postProductTypeValue, postProductMakeValue);
            loadPostProductModels(postProductTypeValue, postProductMakeValue, postProductModelValue);
            $postProductNameEdit.val(postProductNameEditValue);
        });

        $postProductNameEditingArea.on("submit", function (e) {
            e.preventDefault();

            var data = {
                type: $postProductType.val(),
                make: $postProductMake.val(),
                model: $postProductModel.val(),
                engine: $postProductEngine.val(),
                name: $postProductNameEdit.val(),
                hash: AppParams.globalAuthHash
            };

            $.ajax({
                url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId + "/edit",
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (result, textStatus, jqXHR) {
                    var $postProductNameId = $('<span class="post-product-name-id"></span>');
                    $postProductNameId.text("#" + result.universal_id);
                    $postProductName
                        .text(result.make + '  ' + result.model + ' ' + result.engine + ' ' + result.name + ' ')
                        .append($postProductNameId)
                    ;
                    closePostProductNameEdit();

                    if (postProductTypeValue !== result.type) {
                        loadPostProductInfo();
                    }

                    postProductTypeValue = result.type;
                    postProductMakeValue = result.make;
                    postProductModelValue = result.model;
                    postProductNameEditValue = result.name;
                    $postProductType.val(result.type);
                    loadPostProductMakes(result.type, result.make);
                    loadPostProductModels(result.type, result.make, result.model);
                    $postProductNameEdit.val(result.name);

                    // rewrite the breadcrumb
                    var breadCrumbTypeLink = AppParams.webroot + "/search?type=" + encodeURI(result.type);
                    var breadCrumbMakeLink = breadCrumbTypeLink + "&make=" + encodeURI(result.make);
                    var breadCrumbModelLink = breadCrumbMakeLink + "&model=" + encodeURI(result.model);

                    var $breadCrumbType = $('#post-breadcrumb-type a');
                    var $breadCrumbMake = $('#post-breadcrumb-make a');
                    var $breadCrumbModel = $('#post-breadcrumb-model a');

                    $breadCrumbType.text($('#post-product-type option:selected').text());
                    $breadCrumbType.attr("href", breadCrumbTypeLink);

                    $breadCrumbMake.text(result.make);
                    $breadCrumbMake.attr("href", breadCrumbMakeLink);

                    $breadCrumbModel.text(result.model);
                    $breadCrumbModel.attr("href", breadCrumbModelLink);

                    $('#post-breadcrumb-name').text(result.name);
                }
            });
        });

        $postProductInfoEditingArea.on("submit", function (e) {
            e.preventDefault();
            savePostProductInfo();
        });

        var loadPostProductInfo = function () {
            $.ajax({
                url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId +
                    "/edit?show-partial=post-product-info",
                type: 'GET',
                dataType: 'html',
                success: function (result, textStatus, jqXHR) {
                    updatePostProductInfo(result);
                }
            });
        };

        var savePostProductInfo = function () {
            var data = $postProductInfoEditingArea.serialize();

            $.ajax({
                url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId +
                    "/edit?show-partial=post-product-info",
                type: 'POST',
                dataType: 'html',
                data: data,
                success: function (result, textStatus, jqXHR) {
                    updatePostProductInfo(result);
                }
            });
        };

        var openPostProductInfoEdit = function () {
            $postProductInfo.addClass("hidden");
            $postProductInfoEditingArea.removeClass("hidden");
            $('[name="price"]', $postProductInfoEditingArea).focus();
        };

        var closePostProductInfoEdit = function () {
            $postProductInfo.removeClass("hidden");
            $postProductInfoEditingArea.addClass("hidden");
        };

        var updatePostProductInfo = function (result) {
            var $result = $('<div></div>').html(result);

            $postProductInfo.html($("#post-product-info", $result).html());
            $postProductInfoEditingArea.html($("#post-product-info-editing-area", $result).html());
            postProductInfoEditingAreaOriginal = $postProductInfoEditingArea.html();

            $postProductInfoOthers.html($("#post-product-info-others", $result).html());

            loadPostProductInfoEditingAreaElements();
            closePostProductInfoEdit();
        };

        $postProductInfo.on("click", function (e) {
            openPostProductInfoEdit();
        });

        $postProductInfoEditingArea.on("click", '#post-product-info-cancel', function () {
            $postProductInfoEditingArea.html(postProductInfoEditingAreaOriginal);
            loadPostProductInfoEditingAreaElements();
            closePostProductInfoEdit();
        });

        var postDescriptionTextEditValue = $postDescriptionTextEdit.val();

        $postDescriptionTextEdit.autoResize({
            minHeight: 50,
            maxHeight: 400,
            extraSpace: 15
        });

        var openDescriptionEdit = function () {
            $postDescriptionText.addClass("hidden");
            $postDescriptionEditingArea.removeClass("hidden");

            $postDescriptionTextEdit.focus();

            var descriptionLength = $postDescriptionTextEdit.val().length;
            $postDescriptionTextEdit.scrollTop($postDescriptionTextEdit.scrollHeight);

            $postDescriptionTextEdit[0].selectionStart = $postDescriptionTextEdit[0].selectionEnd = descriptionLength;
        };

        var closeDescriptionEdit = function () {
            $postDescriptionText.removeClass("hidden");
            $postDescriptionEditingArea.addClass("hidden");
            $htmlFormattingPopover.popover("hide");
            confirmBeforeExit = false;
        };

        var saveDescriptionEdit = function () {
            $postDescriptionTextSave.button("loading");

            var data = {
                description: $postDescriptionTextEdit.val(),
                hash: AppParams.globalAuthHash
            };

            $.ajax({
                url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId + "/edit",
                type: 'POST',
                dataType: 'json',
                data: data,
                complete: function () {
                    $postDescriptionTextSave.button("reset");
                },
                success: function (result, textStatus, jqXHR) {
                    postDescriptionTextEditValue = result.description;
                    $postDescriptionTextEdit.val(result.description);
                    $postDescriptionText.html(result.description_html_escaped);
                    closeDescriptionEdit();
                }
            });
        };

        var cancelDescriptionEdit = function () {
            $postDescriptionTextEdit.val(postDescriptionTextEditValue);
            closeDescriptionEdit();
        };

        $postDescriptionTextSave.on("click", function (e) {
            saveDescriptionEdit();
        });

        $postDescriptionTextCancel.on("click", function (e) {
            cancelDescriptionEdit();
        });

        $postDescriptionText.on("click", function (e) {
            if (e.target.nodeName.toLowerCase() !== 'a') {
                openDescriptionEdit();
            }
        });

        $postDescriptionTextEdit.on("click", function (e) {
            $htmlFormattingPopover.popover("hide");
        });

        $postDescriptionTextEdit.on("keyup", function (e) {
            if ($postDescriptionTextEdit.val() !== postDescriptionTextEditValue) {
                confirmBeforeExit = true;
            } else {
                confirmBeforeExit = false;
            }
        });

        $postDescriptionTextEdit.on("paste", function (e) {
            if ($postDescriptionTextEdit.val() !== postDescriptionTextEditValue) {
                confirmBeforeExit = true;
            } else {
                confirmBeforeExit = false;
            }
        });

        var setPostStatus = function (status, callbackfn) {
            $.ajax({
                url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId + "/edit",
                type: 'POST',
                dataType: 'json',
                data: {
                    hash: AppParams.globalAuthHash,
                    status: status
                },
                success: function (result, textStatus, jqXHR) {
                    if (result.status) {
                        callbackfn(true);
                    }
                }
            });
        };

        $postStatus.on("click", 'button', function (e) {
            var $target = $(e.target);
            var $button = $target.closest("button");
            var action = $button.data("action");

            switch (action) {
            case "end" :
                setPostStatus("end", function (success) {
                    if (success) {
                        $button.removeClass("btn-danger");
                        $button.addClass("btn-primary");
                        $button.html("Publicar");
                        $button.data("action", "publish");
                        $postStatusStaging.addClass("hidden");
                        $postStatusEnd.removeClass("hidden");
                    }
                });
                break;
            case "publish" :
                setPostStatus("active", function (success) {
                    if (success) {
                        $button.removeClass("btn-primary");
                        $button.addClass("btn-danger");
                        $button.html("Esconder");
                        $button.data("action", "end");
                        $postStatusStaging.addClass("hidden");
                        $postStatusEnd.addClass("hidden");
                        $postStatusInfoStaging.addClass("hidden");
                    }
                });
                break;
            }
        });
    }
);
