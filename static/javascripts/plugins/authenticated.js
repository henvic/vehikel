/*global define */
/*jslint browser: true */

define(["AppParams", "jquery", "models/vehicles", "jquery.maskMoney"], function (AppParams, $, vehiclesModel) {
    "use strict";

    var postNewAd,
        $postNewAdButton;

    /**
     * Logout routine for the logout button on the navbar
     *
     * This overrides the default action which is loading
     * the /logout page in which the user can do remote logout
     * and see its recent activities
     */
    (function () {
        var logoutAnchor = $("#navbar-logout-link");
        logoutAnchor.click(function (e) {
            var params,
                form,
                key,
                hiddenField;

            e.preventDefault();

            params = {
                "hash" : AppParams.globalAuthHash,
                "signout" : "true"
            };

            form = document.createElement("form");

            document.body.appendChild(form);
            form.setAttribute("method", "POST");
            form.setAttribute("action", "/logout");

            for (key in params) {
                if (params.hasOwnProperty(key)) {
                    hiddenField = document.createElement("input");
                    hiddenField.setAttribute("type", "hidden");
                    hiddenField.setAttribute("name", key);
                    hiddenField.setAttribute("value", params[key]);
                    form.appendChild(hiddenField);
                }
            }
            form.submit();
        });
    }());

    postNewAd = function () {
        var $postProductTypeNew = $("#post-product-type-new"),
            $postProductMakeNew = $("#post-product-make-new"),
            $postProductModelNew = $("#post-product-model-new"),
            $postProductPriceNew = $("#post-product-price-new");

        vehiclesModel.setUp($postProductTypeNew, $postProductMakeNew, $postProductModelNew);

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
        $postNewAdButton = $("#post-new-ad-button");

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
                success: function (data) {
                    var $postProductNewNext,
                        $postProductNew;

                    $("body").append($(data));
                    $("#post-product-new-modal").modal();
                    postNewAd();
                    $postProductNewNext = $("#post-product-new-next");
                    $postProductNew = $("#post-product-new");

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
});
