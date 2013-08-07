/*global define, window */
/*jshint indent:4 */

define(["AppParams", "jquery", "underscore", "models/search", "text!templates/search/facets.html"],
    function (AppParams, $, underscore, searchModel, facetsTemplate) {
        "use strict";

        if (! document.getElementById("aside-similar-offers")) {
            //there is no aside-similar-offers navbar
            return;
        }

        //@todo only load this if the screen has the necessary space to show it (it is not hidden)

        var $asideSimilarOffers = $("#aside-similar-offers");

        var formSerialized = "q=";

        if (AppParams.postUsername !== undefined) {
            formSerialized += "&u=" + encodeURIComponent(AppParams.postUsername);
        }

        var xhr = $.ajax({
            data: formSerialized,
            url: AppParams.webroot + "/search-engine?facets",
            type: "GET",
            cache: true
        });

        var loadFacets = function (facets) {
            var compiledFacets = underscore.template(facetsTemplate);

            var facetsHtml = compiledFacets(
                {
                    termListHtmlElements : searchModel.termListHtmlElements,
                    termListHtmlElementsType : searchModel.termListHtmlElementsType,
                    termListHtmlElementsBool : searchModel.termListHtmlElementsBool,
                    formSerialized : formSerialized,
                    transmissionTranslation : searchModel.transmissionTranslation,
                    tractionTranslation : searchModel.tractionTranslation,
                    facets : facets,
                    searchParamsTotal : 0,
                    currentQueryStringParams : searchModel.parseQueryString(formSerialized)
                }
            );

            $asideSimilarOffers.html(facetsHtml);

            var $priceInputs = $(".price-inputs", $asideSimilarOffers);
            var $priceMinInput = $(".price-min-input", $asideSimilarOffers);
            var $priceMaxInput = $(".price-max-input", $asideSimilarOffers);

            $priceInputs.on("keyup", function (e) {
                if (e.keyCode === 13) {
                    var priceMin = $priceMinInput.val().replace(/[^0-9]/g, '');
                    var priceMax = $priceMaxInput.val().replace(/[^0-9]/g, '');

                    var linkParamsArray = [];

                    linkParamsArray.push("q=");

                    if (AppParams.postUsername !== undefined) {
                        linkParamsArray.push("u=" + encodeURIComponent(AppParams.postUsername));
                    }

                    if (priceMin) {
                        linkParamsArray.push("price-min=" + encodeURIComponent(priceMin));
                    }

                    if (priceMax) {
                        linkParamsArray.push("price-max=" + encodeURIComponent(priceMax));
                    }

                    window.location = AppParams.webroot + "/search?" + linkParamsArray.join("&");
                }
            });

            $priceInputs.tooltip();

            searchModel.maskMoney($priceMinInput);
            searchModel.maskMoney($priceMaxInput);
        };

        xhr.done(function (response) {
            loadFacets(response.facets);
        });
    }
);