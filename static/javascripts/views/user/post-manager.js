/*global define, Modernizr */
/*jshint indent:4 */

define([
    'AppParams',
    'jquery',
    'yui',
    'underscore',
    'text!templates/help/html.html',
    'text!templates/posts/manager-gallery.html',
    'text!templates/posts/manager-picture.html',
    'jquery.maskMoney'
],
    function (
        AppParams,
        $,
        YUI,
        underscore,
        htmlTemplate,
        postsManagerGalleryTemplate,
        postsManagerPictureTemplate
        ) {
        "use strict";

        /**
         * Convert number of bytes into human readable format
         * from http://codeaid.net/javascript/convert-size-in-bytes-to-human-readable-format-(javascript)
         *
         * @param integer bytes Number of bytes to convert
         * @param integer precision Number of digits after the decimal separator
         * @return string
         */
        var bytesToSize = function (bytes, precision) {
            var kilobyte = 1024;
            var megabyte = kilobyte * 1024;
            var gigabyte = megabyte * 1024;
            var terabyte = gigabyte * 1024;

            if ((bytes >= 0) && (bytes < kilobyte)) {
                return bytes + ' B';

            } else if ((bytes >= kilobyte) && (bytes < megabyte)) {
                return (bytes / kilobyte).toFixed(precision) + ' KB';

            } else if ((bytes >= megabyte) && (bytes < gigabyte)) {
                return (bytes / megabyte).toFixed(precision) + ' MB';

            } else if ((bytes >= gigabyte) && (bytes < terabyte)) {
                return (bytes / gigabyte).toFixed(precision) + ' GB';

            } else if (bytes >= terabyte) {
                return (bytes / terabyte).toFixed(precision) + ' TB';

            } else {
                return bytes + ' B';
            }
        };

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

        var updatePostItem = function (name, value) {
            var data =
                encodeURIComponent(name) + "=" + encodeURIComponent(value) +
                    "&hash=" +
                    encodeURIComponent(AppParams.globalAuthHash)
                ;

            // @todo add success and failure handling
            var xhr = $.ajax({
                url: AppParams.webroot + "/" + AppParams.postUsername + "/" + AppParams.postId + "/edit",
                type: 'POST',
                dataType: 'json',
                data: data
            });

            return xhr;
        };

        $postProductInfoOthers.on("click", '.label-checkbox [type="checkbox"]', function (e) {
            var isChecked = e.target.checked;
            var $labelTarget = $(e.target.parentNode);

            if (isChecked) {
                $labelTarget.removeClass("has-not").addClass("has");
            } else {
                $labelTarget.removeClass("not").addClass("has-not");
            }

            updatePostItem(e.target.name, (isChecked) ? 1 : 0);
        });

        var updatePostEquipments = function () {
            var equipment = $('.post-equipments-list [name="equipment[]"]', $postProductMainInfo).serialize();

            var data = equipment + "&hash=" + encodeURIComponent(AppParams.globalAuthHash);

            // @todo add success and failure handling
            $.ajax({
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

            loadPostProductModels($postProductType.val(), $postProductMake.val());
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
                        var fileSize = bytesToSize(fileInstance.get("size"), 2);

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
                    $("#upload-file").addClass("disabled").off("click");
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

                    $uploadFile.on("click", function () {
                        if (! uploadDone && uploader.get("fileList").length > 0) {
                            uploader.uploadAll();
                        }
                    });
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
                Galleria.ready(function (options) {
                    var gallery = this;
                    gallery.splice(0);
                    gallery.push(response.gallery);
                    console.log("done reload images");
                    console.log(response.gallery);
                });
            });
        };

        var addPicture = function (picture) {
            if (picture.type === "video") {
                setVideoIdValue(picture.id);
                return "";
            } else {
                return compiledPostsManagerPictureTemplate({
                    picture: picture
                });
            }
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

        var parseYouTubeIdFromLink = function (link, softPass) {
            //from http://stackoverflow.com/posts/10591582/revisions
            var id = link.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/);
            if(id !== null) {
                return id[1];
            } else if (softPass) {
                return link;
            } else {
                return "";
            }
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
                $(videoLinkEl).addClass("video-link-feedback-done");
                setTimeout(function () {
                    $(videoLinkEl).removeClass("video-link-feedback-done");
                }, 1000);
            });

            post.fail(function () {
                $(videoLinkEl).addClass("video-link-feedback-fail");
                setTimeout(function () {
                    $(videoLinkEl).removeClass("video-link-feedback-fail");
                }, 1000);
            });
        };

        var createThumbnails = function ($element, pictures) {
            var picturesLength = pictures.length;

            var picturesDiv = "";
            for (var counter = 0; counter < picturesLength; counter++) {
                picturesDiv += addPicture(pictures[counter]);
            }

            $element.html(compiledpostsManagerGalleryTemplate({
                picturesDiv : picturesDiv,
                isTouch : Modernizr.touch,
                videoLink : buildVideoLink(videoId)
            }));
        };

        var setUpGalleryManager = function () {
            compiledPostsManagerPictureTemplate = underscore.template(postsManagerPictureTemplate);
            compiledpostsManagerGalleryTemplate = underscore.template(postsManagerGalleryTemplate);
            videoLinkEl = $galleryManager[0].getElementsByClassName("video-link");

            pictures = AppParams.postGalleryImages;

            $galleryManager.on("click", ".image .action-delete", function (e) {
                var pictureId = e.target.parentNode.getAttribute("data-id");
                erasePicture(pictureId);
            });

            $galleryManager.on("click", ".video .action-delete", function (e) {
                updateVideoId("");
            });

            $galleryManager.on("submit", ".video-form", function (e) {
                e.preventDefault();
                var youTubeId = parseYouTubeIdFromLink(getVideoLinkElement().value, true);
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

            getVideoLinkElement().value = window.prompt("Link para vídeo do YouTube?", getVideoLinkElement().value);

            $(".video-form", $galleryManager).submit();
        });
    }
);
