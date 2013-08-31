/*global define */
/*jslint browser: true */

define(["AppParams", "jquery", "underscore", "models/search", "text!templates/search/facets.html"],
    function (AppParams, $, underscore, searchModel, facetsTemplate) {
        "use strict";

        if (!document.getElementById("aside-similar-offers")) {
            //there is no aside-similar-offers navbar
            return;
        }

        var $asideSimilarOffers = $("#aside-similar-offers"),
            formSerialized = "q=",
            xhr,
            loadFacets;

        if (AppParams.postUsername !== undefined) {
            formSerialized += "&u=" + encodeURIComponent(AppParams.postUsername);
        }

        xhr = $.ajax({
            data: formSerialized,
            url: AppParams.webroot + "/search-engine?facets",
            type: "GET",
            cache: true
        });

        loadFacets = function (facets) {
            var compiledFacets,
                facetsHtml,
                $priceInputs,
                $priceMinInput,
                $priceMaxInput;

            compiledFacets = underscore.template(facetsTemplate);

            facetsHtml = compiledFacets(
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

            $priceInputs = $(".price-inputs", $asideSimilarOffers);
            $priceMinInput = $(".price-min-input", $asideSimilarOffers);
            $priceMaxInput = $(".price-max-input", $asideSimilarOffers);

            $priceInputs.on("keyup", function (e) {
                var priceMin,
                    priceMax,
                    linkParamsArray = [];

                if (e.keyCode === 13) {
                    priceMin = $priceMinInput.val().match(/[\d]/g).join("");
                    priceMax = $priceMaxInput.val().match(/[\d]/g).join("");

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
    });