/*global define, window */
/*jshint indent:4 */

define(["AppParams", "jquery", "models/vehicles", "jquery.maskMoney"], function (AppParams, $, vehiclesModel) {
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
