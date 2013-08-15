/*global define, Modernizr, Galleria, CKEDITOR */
/*jshint indent:4 */

define([
    'AppParams',
    'jquery',
    'yui',
    'underscore',
    'models/vehicles',
    'models/files',
    'plugins/ckeditor-config',
    'text!templates/posts/manager-gallery.html',
    'text!templates/posts/manager-picture.html',
    'text!templates/posts/crop-picture.html',
    'jcrop',
    'jquery.maskMoney'
],
    function (
        AppParams,
        $,
        YUI,
        underscore,
        vehiclesModel,
        filesModel,
        ckeditorConfig,
        postsManagerGalleryTemplate,
        postsManagerPictureTemplate,
        cropPictureTemplate
        ) {
        "use strict";

        var $postProductType = $('#post-product-type');
        var $postProductMake = $('#post-product-make');
        var $postProductModel = $('#post-product-model');
        var $postProductEngine = $('#post-product-engine');

        var $postProductName = $('#post-product-name');
        var $postProductNameEdit = $('#post-product-name-edit');
        var $postProductNameEditingArea = $('#post-product-name-editing-area');

        var $postProductMainInfo = $("#post-product-main-info");
        var $postProductInfo = $("#post-product-info");
        var $postProductInfoValue = $("#post-product-info .value");
        var $postProductInfoOthers = $("#post-product-info-others");

        var $postDescriptionText = $('#post-description-text');
        var $postDescriptionTextEdit = $('#post-description-text-edit');
        var $postDescriptionEditingArea = $('#post-description-editing-area');
        var $postDescriptionTextSave = $('#post-description-text-save');

        var $postStatus = $('#post-status');

        var postProductMakeValue = $postProductMake.val();
        var postProductModelValue = $postProductModel.val();
        var postProductNameEditValue = $postProductNameEdit.val();

        var $editPostButton = $("#edit-post-button");

        var updatePostItem = function (name, value) {
            var data =
                encodeURIComponent(name) + "=" + encodeURIComponent(value) +
                    "&hash=" +
                    encodeURIComponent(AppParams.globalAuthHash)
                ;

            return $.ajax({
                url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId + "/edit",
                type: 'POST',
                dataType: 'json',
                data: data
            });
        };

        $postProductInfoOthers.on("click", '.label-checkbox [type="checkbox"]', function (e) {
            updatePostItem(e.target.name, (e.target.checked) ? 1 : 0);
        });

        var lastUpdatePostEquipmentsCall;

        var updatePostEquipments = function () {
            if (lastUpdatePostEquipmentsCall) {
                lastUpdatePostEquipmentsCall.abort();
            }

            var equipment = $('.post-equipments-list [name="equipment[]"]', $postProductMainInfo).serialize();

            if (equipment === "") {
                equipment = 'equipment%5B%5D=';
            }

            var data = equipment + "&hash=" + encodeURIComponent(AppParams.globalAuthHash);

            // @todo add success and failure handling
            lastUpdatePostEquipmentsCall = $.ajax({
                url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId + "/edit",
                type: 'POST',
                dataType: 'json',
                data: data
            });
        };

        $postProductMainInfo.on("click", '.post-equipments-list [type="checkbox"]', function (e) {
            var isChecked = e.target.checked;
            var $labelTarget = $(e.target.parentNode);

            if (isChecked) {
                $labelTarget.removeClass("has-not").addClass("has");
            } else {
                $labelTarget.removeClass("not").addClass("has-not");
            }

            updatePostEquipments();
        });

        $editPostButton.on("click", function (e) {
            openPostProductNameEdit();
            openDescriptionEdit();
            $(window).scrollTop($postProductNameEditingArea.position().top);
        });

        vehiclesModel.maskMoney($("#post-product-info #price"));

        vehiclesModel.setUp($postProductType, $postProductMake, $postProductModel);

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

        $postProductNameEditingArea.on("submit", function (e) {
            e.preventDefault();

            var data = {
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
                    var breadCrumbTypeLink = AppParams.webroot + "/search?type=" + encodeURI(result.type);
                    var breadCrumbMakeLink = breadCrumbTypeLink + "&make=" + encodeURI(result.make);
                    var breadCrumbModelLink = breadCrumbMakeLink + "&model=" + encodeURI(result.model);

                    $(".make-a", $postProductName).text(result.make).attr("href", breadCrumbMakeLink);
                    $(".make", $postProductName).text(result.make);
                    $(".model-a", $postProductName).text(result.model).attr("href", breadCrumbModelLink);
                    $(".model", $postProductName).text(result.model);
                    $(".engine", $postProductName).text(result.engine);
                    $(".name", $postProductName).text(result.name);

                    closePostProductNameEdit();

                    postProductMakeValue = result.make;
                    postProductModelValue = result.model;
                    postProductNameEditValue = result.name;
                    vehiclesModel.loadPostProductMakes($postProductMake, result.type, result.make);
                    vehiclesModel.loadPostProductModels($postProductModel, result.type, result.make, result.model);
                    $postProductNameEdit.val(result.name);

                    window.document.title =
                        result.make + " " +
                        result.model + " " +
                        result.engine + " " +
                        result.name + " – " +
                        AppParams.applicationname
                    ;
                }
            });
        });

        var closeProductInfoField = function (name) {
            //@todo escape the unescaped name var
            var $value = $('[data-name="' + name + '"] .value');

            var $action = $(".action", $value);
            var $textValue = $(".text-value", $value);
            var $editableValue = $(".editable-value", $value);

            $value.addClass("value-on");
            $textValue.removeClass("none");
            $editableValue.addClass("none");
            $action.addClass("edit-button").removeClass("save-button");
            $(".icon-ok", $action).addClass("icon-edit").removeClass("icon-ok");
        };

        var filterProductInfoFieldData = function (name, value) {
            if (name === "price" && value.substr(0, 3) === "R$ ") {
                value = value.substr(3);
            }

            return value;
        };

        var updateProductInfoField = function (name) {
            //@todo escape the unescaped name var
            var $inputField = $('[name="' + name + '"]', $postProductInfo);
            var $value = $('[data-name="' + name + '"] .value');

            var $textValue = $(".text-value", $value);

            var enteredValue = $inputField.val();

            if ($value.data("saved-value").toString() === enteredValue) {
                setTimeout(function () {
                    closeProductInfoField(name);
                }, 100);
                return;
            }

            var value = filterProductInfoFieldData(name, enteredValue);

            var post = updatePostItem(name, value);

            post.done(function () {
                $($inputField).addClass("input-field-feedback-done");
                var $action = $(".action", $value);
                $action.removeAttr("tabindex");
                setTimeout(function () {
                    $($inputField).removeClass("input-field-feedback-done");

                    var printName = enteredValue;

                    if ($inputField[0].nodeName && $inputField[0].nodeName.toLowerCase() === "select") {
                        printName = $(':selected', $inputField).text();
                    }

                    if (printName) {
                        $textValue.text(printName);
                    }

                    if (! enteredValue) {
                        $textValue.html('<span class="muted">-</span>');
                    }

                    closeProductInfoField(name);
                    $value.data("saved-value", enteredValue);
                }, 500);
            });

            post.fail(function () {
                $($inputField).addClass("input-field-feedback-fail");
                setTimeout(function () {
                    $($inputField).removeClass("input-field-feedback-fail");
                }, 500);
            });
        };

        $postProductInfoValue.on("click", function (e) {
            var $value = $(this.parentNode.getElementsByClassName("value")[0]);
            var $action = $(".action", $value);

            if ($action.hasClass("edit-button")) {
                var $textValue = $(".text-value", $value);
                var $editableValue = $(".editable-value", $value);

                $value.removeClass("value-on");
                $textValue.addClass("none");
                $editableValue.removeClass("none").focus();
                $action.removeClass("edit-button").addClass("save-button").attr("tabindex", "-1");
                $(".icon-edit", $action).removeClass("icon-edit").addClass("icon-ok");

                return;
            }

            if ($action.hasClass("save-button")) {
                var targetName = e.target.nodeName.toLowerCase();
                if (targetName === "button" || targetName === "i") {
                    var name = this.parentNode.getAttribute("data-name");
                    updateProductInfoField(name);
                }
            }
        });

        $postProductInfoValue.on("focusout", function (e) {
            var name = e.target.parentNode.parentNode.getAttribute("data-name");
            updateProductInfoField(name);
        });

        $postProductInfoValue.on("keydown", function (e) {
            if (e.target.nodeName.toLowerCase() !== "button" && e.keyCode === 13) {
                var name = e.target.parentNode.parentNode.getAttribute("data-name");
                updateProductInfoField(name);
            }

            //on the tab key go to next
            if (e.keyCode === 9) {
                var $nextItem;

                //if the shift key is being pressed, let's go back instead
                if (e.shiftKey) {
                    $nextItem = $(e.target.parentNode).parent('tr').prev();
                } else {
                    $nextItem = $(e.target.parentNode).parent('tr').next();
                }

                if ($nextItem[0] !== undefined) {
                    $nextItem.children(".value").click();
                    $nextItem.find(".value .editable-value").focus();
                    e.preventDefault();
                }
            }
        });

        var postDescriptionTextEditValue = $postDescriptionTextEdit.val();

        var editor;

        CKEDITOR.on("instanceReady", function (ev) {
            editor = CKEDITOR.instances["post-description-text-edit"];
        });

        var openDescriptionEdit = function () {
            $postDescriptionText.addClass("hidden");
            $postDescriptionEditingArea.removeClass("hidden");

            $postDescriptionTextEdit.focus();

            editor.focus();

            //see http://stackoverflow.com/questions/8914543/ckeditor-cursor-position-after-inserting-uneditable-element

            var s = editor.getSelection();
            var selected_ranges = s.getRanges();
            var node = selected_ranges[0].startContainer;
            var parents = node.getParents(true);

            node = parents[parents.length - 2].getFirst();

            while (true) {
                var x = node.getNext();
                if (x === null) {
                    break;
                }
                node = x;
            }

            s.selectElement(node);
            selected_ranges = s.getRanges();
            selected_ranges[0].collapse(false);
            s.selectRanges(selected_ranges);
        };

        var closeDescriptionEdit = function () {
            $postDescriptionText.removeClass("hidden");
            $postDescriptionEditingArea.addClass("hidden");
        };

        var saveDescriptionEdit = function () {
            $postDescriptionTextSave.button("loading");

            var content = editor.getData();

            $postDescriptionTextEdit.val(content);

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
                    editor.setData(result.description);
                    $postDescriptionText.html(result.description_html_escaped);
                    closeDescriptionEdit();
                }
            });
        };

        $postDescriptionTextSave.on("click", function (e) {
            saveDescriptionEdit();
        });

        $postDescriptionText.on("click", function (e) {
            if (e.target.nodeName.toLowerCase() !== 'a') {
                openDescriptionEdit();
            }
        });

        CKEDITOR.replace("post-description-text-edit", ckeditorConfig);

        var lastUpdateStatusCall;

        $("[name=status]", $postStatus).on("change", function (e) {
            if (lastUpdateStatusCall) {
                lastUpdateStatusCall.abort();
            }

            lastUpdateStatusCall = updatePostItem("status", this.value);
        });

        YUI({filter:"raw"}).use("uploader", function(Y) {

            if (Y.Uploader.TYPE !== "none" && !Y.UA.ios) {
                var uploader = new Y.Uploader({width: "250px",
                    height: "35px",
                    multipleFiles: true,
                    swfURL: "http://yui.yahooapis.com/3.9.1/build/uploader/assets/flashuploader.swf?t=" + Math.random(),
                    uploadURL: AppParams.webroot + "/" + AppParams.postUsername + "/" +
                        AppParams.postId + "/picture/add",
                    postVarsPerFile: {hash: AppParams.globalAuthHash},
                    fileFilters: {
                        description: "Images",
                        extensions: "*.jpg;*.jpeg;*.gif;*.png"
                    },
                    simLimit: 2,
                    withCredentials: false
                });

                var uploadDone = false;

                if (Y.Uploader.TYPE === "html5") {
                    uploader.set("dragAndDropArea", "body");
                }

                var foo = document.createElement("div");
                uploader.render(foo);

                var $uploadFile = $("#upload-file");

                $uploadFile.on("click", function () {
                    uploader.openFileSelectDialog();
                });

                uploader.after("fileselect", function (event) {

                    var fileList = event.fileList;

                    var fileTable = Y.one("#file-names tbody");

                    if (uploadDone) {
                        uploadDone = false;
                        fileTable.setHTML('');
                    }

                    Y.each(fileList, function (fileInstance) {
                        var fileSize = filesModel.convertBytesToSize(fileInstance.get("size"), 2);

                        fileTable.append('<tr id="'+ fileInstance.get("id") + "_row" + '"><td><strong>' +
                            fileInstance.get("name") + '</strong></td>' +
                            '<td>' + fileSize + '</td>' +
                            '<td><div class="progress progress-striped active">' +
                            '<div class="percentdone bar" style="width: 0%"></div>' +
                            '</div></td>' +
                            '</tr>');
                    });

                    if (! uploadDone && uploader.get("fileList").length > 0) {
                        uploader.uploadAll();
                        Y.one("#filelist").removeClass("hidden");
                    }
                });

                uploader.on("uploadprogress", function (event) {
                    var fileRow = Y.one("#" + event.file.get("id") + "_row");
                    fileRow.one(".percentdone").setStyle("width", event.percentLoaded + "%");
                });

                uploader.on("uploadstart", function (event) {
                    uploader.set("enabled", false);
                    $("#upload-file").addClass("disabled");
                });

                uploader.on("uploadcomplete", function (event) {
                    var fileRow = Y.one("#" + event.file.get("id") + "_row");
                    fileRow.one(".percentdone").setStyle("width", "100%").removeClass("active");
                });

                uploader.on("alluploadscomplete", function (event) {
                    uploader.set("enabled", true);
                    uploader.set("fileList", []);

                    $uploadFile.removeClass("disabled");

                    setTimeout(function () {
                        var fileTable = Y.one("#file-names tbody");
                        fileTable.setHTML('');
                        Y.one("#filelist").addClass("hidden");
                        reloadImages();
                    }, 1000);
                    uploadDone = true;
                });
            }
            else {
                Y.one("#uploaderContainer").set("text", "Seu browser não suporta upload de imagens.");
            }
        });

        YUI().use('dd-constrain', 'dd-proxy', 'dd-drop', 'dd-delegate', 'dd-drop-plugin', function (Y) {
            Y.DD.DDM.on('drop:over', function (e) {
                var drag = e.drag.get('node'),
                    drop = e.drop.get('node');

                if (drop.get('tagName').toLowerCase() === 'li') {
                    if (! goingUp) {
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
                goingUp = (y < lastY);
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

                var orderLis = Y.Node.all('#gallery-manager .pictures-thumbnails .picture');
                var ids = [];
                orderLis.each(function (v, k) {
                    ids.push(v.getData("id"));
                });

                var post = $.ajax({
                    url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId + "/picture/sort",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        hash: AppParams.globalAuthHash,
                        picture_id: ids
                    }
                });

                post.done(function () {
                    reloadImages();
                });

                post.fail(function () {
                    reloadImages();
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
                container: '.pictures-thumbnails',
                nodes: '.picture',
                target: {
                    padding: '0 0 0 20'
                }
            });

            del.dd.plug(Y.Plugin.DDProxy, {
                moveOnEnd: false,
                cloneNode: true
            });

            del.dd.plug(Y.Plugin.DDConstrained, {
                constrain2node: '.pictures-thumbnails'
            });

            del.dd.plug(Y.Plugin.Drop);
        });

        var compiledPostsManagerPictureTemplate;

        var compiledpostsManagerGalleryTemplate;

        var $galleryManager = $("#gallery-manager");

        var videoLinkEl;

        var pictures;

        var videoId;

        var reloadImages = function () {
            var post = $.ajax({
                url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId,
                type: 'GET',
                dataType: 'json',
                data: ({
                    format: "json",
                    gallery: true
                })
            });

            post.done(function (response) {
                AppParams.postGalleryImages = response.gallery;
                var $thumbnails = $(".pictures-thumbnails", $("#gallery-manager"));
                $thumbnails.html(drawThumbnails(response.gallery));
                Galleria.ready(function (options) {
                    var gallery = this;
                    gallery.splice(0);
                    gallery.push(response.gallery);
                });
            });
        };

        var cutPicture = function (pictureId, cut) {
            return $.ajax({
                url: AppParams.webroot + "/" + AppParams.postUsername + "/" +
                    AppParams.postId + "/picture/edit",
                type: 'POST',
                dataType: 'json',
                data: {
                    hash: AppParams.globalAuthHash,
                    picture_id: pictureId,
                    x : cut.x,
                    y : cut.y,
                    x2 : cut.x2,
                    y2 : cut.y2,
                    w : cut.w,
                    h : cut.h
                }
            });
        };

        var addPicture = function (picture) {
            return compiledPostsManagerPictureTemplate({
                picture: picture
            });
        };

        var editPicture = function (pictureId) {
            var picture = underscore.find(AppParams.postGalleryImages,
                function (eachPicture) {
                    return eachPicture.id === pictureId;
                }
            );

            var compiledTemplate = underscore.template(cropPictureTemplate);
            $("body").append(compiledTemplate({
                imageSrc: picture.original
            }));

            var $cropPictureModal = $("#crop-picture-modal");

            $cropPictureModal.modal();

            $cropPictureModal.on("shown", function () {
                var $cropImage = $(".crop-image", $cropPictureModal);

                var jCropApi;

                $cropPictureModal.on("hidden", function (e) {
                    jCropApi.destroy();
                    $cropPictureModal.off();
                    $cropImage.off();
                    $cropPictureModal.remove();
                });

                var width = picture.original_size.width;
                var height = picture.original_size.height;
                var selected = false;

                var setRelease = function () {
                    selected = false;
                };

                var setSelect = function () {
                    selected = true;
                };

                setTimeout(function () {
                    $cropImage.Jcrop({
                        aspectRatio: 4 / 3,
                        trueSize: [width, height],
                        onRelease: setRelease,
                        onSelect: setSelect
                    }, function () {
                        jCropApi = this;

                        var cropOptions = picture.crop_options;

                        var crop = {
                            x : width * 0.1,
                            y : height * 0.1,
                            x2 : width * 0.9,
                            y2 : height * 0.9
                        };

                        if (cropOptions && cropOptions.w > 0 && cropOptions.h > 0) {
                            crop = {
                                x : cropOptions.x,
                                y : cropOptions.y,
                                x2 : cropOptions.x2,
                                y2 : cropOptions.y2
                            };
                        }

                        jCropApi.animateTo([crop.x, crop.y, crop.x2, crop.y2]);
                        selected = true;
                    });
                }, 300);

                $(".clear-selection", $cropPictureModal).on("click", function (e) {
                    jCropApi.release();
                });

                $(".cut", $cropPictureModal).on("click", function (e) {
                    var cut;

                    if (selected) {
                        cut = jCropApi.tellSelect();
                    } else {
                        cut = {x: 0, y: 0, x2: 0, y2: 0, w: 0, h: 0};
                    }

                    var cutResponse = cutPicture(pictureId, {
                        x: Math.floor(cut.x),
                        y: Math.floor(cut.y),
                        x2: Math.floor(cut.x2),
                        y2: Math.floor(cut.y2),
                        w: Math.floor(cut.w),
                        h: Math.floor(cut.h)
                    });

                    cutResponse.done(function () {
                        $cropPictureModal.modal("hide");
                        reloadImages();
                    });
                });
            });
        };

        var erasePicture = function (pictureId) {
            var post = $.ajax({
                url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId + "/picture/delete",
                type: 'POST',
                dataType: 'json',
                data: ({
                    hash: AppParams.globalAuthHash,
                    picture_id: pictureId
                })
            });

            post.done(function () {
                reloadImages();
            });

            post.fail(function () {
                reloadImages();
            });
        };

        var buildVideoLink = function (id) {
            return (id) ? "http://www.youtube.com/watch?v=" + encodeURIComponent(videoId) : "";
        };

        var getVideoLinkElement = function () {
            return videoLinkEl[0];
        };

        var setVideoIdValue = function (newVideoId) {
            videoId = newVideoId;

            var el = getVideoLinkElement();

            if (el !== undefined) {
                el.value = buildVideoLink(newVideoId);
            }
        };

        var updateVideoId = function (newVideoId) {
            setVideoIdValue(newVideoId);

            var post = updatePostItem("youtube_video", newVideoId);

            post.done(function () {
                reloadImages();
                $(videoLinkEl).addClass("input-field-feedback-done");
                setTimeout(function () {
                    $(videoLinkEl).removeClass("input-field-feedback-done");
                }, 500);
            });

            post.fail(function () {
                $(videoLinkEl).addClass("input-field-feedback-fail");
                setTimeout(function () {
                    $(videoLinkEl).removeClass("input-field-feedback-fail");
                }, 500);
            });
        };

        var drawThumbnails = function (pictures) {
            var picturesLength = pictures.length;

            var picturesDiv = "";
            for (var counter = 0; counter < picturesLength; counter++) {
                if (pictures[counter].type === "video") {
                    setVideoIdValue(pictures[counter].id);
                } else if (pictures[counter].type === "image" && pictures[counter].placeholder === undefined) {
                    picturesDiv += addPicture(pictures[counter]);
                }
            }

            return picturesDiv;
        };

        var createThumbnails = function ($element, pictures) {
            $element.html(compiledpostsManagerGalleryTemplate({
                picturesDiv : drawThumbnails(pictures),
                isTouch : Modernizr.touch,
                videoLink : buildVideoLink(videoId)
            }));
        };

        var setUpGalleryManager = function () {
            compiledPostsManagerPictureTemplate = underscore.template(postsManagerPictureTemplate);
            compiledpostsManagerGalleryTemplate = underscore.template(postsManagerGalleryTemplate);
            videoLinkEl = $galleryManager[0].getElementsByClassName("video-link");

            pictures = AppParams.postGalleryImages;

            $galleryManager.on("click", ".image .action-edit", function (e) {
                var pictureId = e.target.parentNode.parentNode.getAttribute("data-id");
                editPicture(pictureId);
            });

            $galleryManager.on("click", ".image .action-delete", function (e) {
                var pictureId = e.target.parentNode.parentNode.getAttribute("data-id");
                erasePicture(pictureId);
            });

            $galleryManager.on("submit", ".video-form", function (e) {
                e.preventDefault();
                var youTubeId = vehiclesModel.parseYouTubeIdFromLink(getVideoLinkElement().value, true);
                updateVideoId(youTubeId);
            });

            createThumbnails($galleryManager, pictures);
        };

        setUpGalleryManager();

        var $openGalleryManager = $("#open-gallery-manager");

        $openGalleryManager.on("click", function (e) {
            if ($galleryManager.hasClass("hidden")) {
                $galleryManager.removeClass("hidden");
                $openGalleryManager.addClass("active");
                $("i", $openGalleryManager).removeClass("icon-plus").addClass("icon-minus");
                $('html, body').animate({
                    scrollTop: $openGalleryManager.offset().top - 90
                }, 400);
            } else {
                $galleryManager.addClass("hidden");
                $openGalleryManager.removeClass("active");
                $("i", $openGalleryManager).removeClass("icon-minus").addClass("icon-plus");
            }
        });

        var $setYouTubeVideo = $("#set-youtube-video");

        $setYouTubeVideo.on("click", function (e) {
            if ($galleryManager.hasClass("hidden")) {
                $openGalleryManager.click();
            }

            getVideoLinkElement().value = window.prompt("Link do YouTube:", getVideoLinkElement().value);

            $(".video-form", $galleryManager).submit();
        });
    }
);
