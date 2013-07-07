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

        var formSerialized = "q=*";

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
        };

        xhr.done(function (response) {
            loadFacets(response.facets);
        });
    }
);