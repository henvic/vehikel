/*global define, window */
/*jshint indent:4 */

define(["AppParams", "jquery", "jquery.maskMoney"], function (AppParams, $) {
    "use strict";

    /**
     * Logout routine for the logout button on the navbar
     *
     * This overrides the default action which is loading
     * the /logout page in which the user can do remote logout
     * and see its recent activities
     */
    var navbarLogoutClosure = (function () {
        var logoutAnchor = $("#navbar-logout-link");
        logoutAnchor.click(function (e) {
            e.preventDefault();

            var params = { "hash" : AppParams.globalAuthHash, "signout" : "true" };
            var form = document.createElement("form");
            document.body.appendChild(form);
            form.setAttribute("method", "POST");
            form.setAttribute("action", "/logout");

            for (var key in params) {
                if (params.hasOwnProperty(key))
                {
                    var hiddenField = document.createElement("input");
                    hiddenField.setAttribute("type", "hidden");
                    hiddenField.setAttribute("name", key);
                    hiddenField.setAttribute("value", params[key]);
                    form.appendChild(hiddenField);
                }
            }
            form.submit();
        });
    } ());

    var postNewAd = function () {
        var $postProductTypeNew = $("#post-product-type-new");
        var $postProductMakeNew = $("#post-product-make-new");
        var $postProductModelNew = $("#post-product-model-new");
        var $postProductPriceNew = $("#post-product-price-new");

        $postProductTypeNew.on("change", function (e) {
            $postProductMakeNew.val("");
            $postProductMakeNew.attr("disabled", "disabled");
            $postProductModelNew.val("");
            $postProductModelNew.attr("disabled", "disabled");
            loadPostProductMakes($postProductTypeNew.val());
        });




            $.ajax({
                url: AppParams.webroot + "/typeahead",
                type: "GET",
                dataType: "json",
                data: ({
                    search: "makes",
                    type: type
                }),
                success: function (data, textStatus, jqXHR) {
                    if (data.values instanceof Array) {
                        var $optGroup = $($postProductMakeNew.find("optgroup")[0]);
                        var $entrySet = $("<select>");
                        $entrySet.append('<option value="">-</option>');
                        $.each(data.values, function (key, value) {
                            $entrySet.append($("<option>", { value : value }).text(value));
                        });

                        $entrySet.append($("<option>", { "data-action" : "other" }).text("Outro"));

                        $postProductMakeNew.removeAttr("disabled");
                        $optGroup.html($entrySet.children());

                        if (make !== undefined) {
                            $postProductMakeNew.val(make);

                            if ($postProductMakeNew.val() === make) {
                                return;
                            }

                            $optGroup.append($("<option>", { value : make }).text(make));
                            $postProductMakeNew.val(make);
                        }
                    }
                }
            });
        };

        var loadPostProductModels = function (type, make, model) {
            $.ajax({
                url: AppParams.webroot + "/typeahead",
                type: "GET",
                dataType: "json",
                data: ({
                    search: "models",
                    type: type,
                    make: make
                }),
                success: function (data, textStatus, jqXHR) {
                    if (data.values instanceof Array) {
                        $postProductModelNew.html('<optgroup label="Modelo"><option value="">-</option></optgroup>');
                        var $optGroup = $($postProductModelNew.find("optgroup")[0]);
                        var $entrySet = $("<select>");
                        $.each(data.values, function (key, value) {
                            $entrySet.append($("<option>", { value : value }).text(value));
                        });

                        $entrySet.append($("<option>", { "data-action" : "other" }).text("Outro"));

                        $postProductModelNew.removeAttr("disabled");
                        $optGroup.append($entrySet.children());

                        if (model !== undefined) {
                            $postProductModelNew.val(model);

                            if ($postProductModelNew.val() === model) {
                                return;
                            }

                            $optGroup.append($("<option>", { value : model }).text(model));
                            $postProductModelNew.val(model);
                        }
                    }
                }
            });
        };

        loadPostProductMakes($postProductTypeNew.val(), $postProductMakeNew.val());
        loadPostProductModels($postProductTypeNew.val(), $postProductMakeNew.val(), $postProductModelNew.val());

        $postProductMakeNew.on("change", function (e) {
            $postProductModelNew.val("");
            $postProductModelNew.attr("disabled", "disabled");

            loadPostProductModels($postProductTypeNew.val(), $postProductMakeNew.val());
        });

        $postProductPriceNew.maskMoney(
            {
                symbol: 'R$',
                thousands: '.',
                decimal: ',',
                defaultZero: false
            }
        );
    };

    if (window.location.pathname === AppParams.webroot + "/new") {
        postNewAd();
    } else {
        var $postNewAdButton = $("#post-new-ad-button");

        $postNewAdButton.one("click", function (e) {
            // if the viewport is too small, don't use modal
            // we need to check this also in the next times the elements are clicked
            if (document.documentElement.clientWidth < 820) {
                return;
            }

            e.preventDefault();
            $.ajax({
                url: AppParams.webroot + "/new",
                type: "GET",
                success: function (data, textStatus, jqXHR) {
                    $("body").append($(data));
                    $("#post-product-new-modal").modal();
                    postNewAd();
                    var $postProductNewNext = $("#post-product-new-next");
                    var $postProductNew = $("#post-product-new");

                    $postProductNewNext.on("click", function () {
                        $postProductNew.submit();
                    });
                    $postNewAdButton.on("click", function (e) {
                        if (document.documentElement.clientWidth < 820) {
                            return;
                        }
                        e.preventDefault();
                        $("#post-product-new-modal").modal();
                    });
                }
            });
        });
    }

    return function () {};
});
