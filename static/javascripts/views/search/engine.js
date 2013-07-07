/*global define, require */
/*jshint indent:4 */

define(
    [
        "AppParams",
        "jquery",
        "underscore",
        "models/search",
        "text!templates/search/results.html",
        "text!templates/search/facets.html"
    ],
    function (AppParams, $, underscore, searchModel, resultsTemplate, facetsTemplate) {
        "use strict";

        var $body = $("body");
        var $searchPostsForm = $("#search-posts-form");
        var $searchText = $("#search-text");
        var $searchTypesNames = $('[name="type"]', $searchPostsForm);
        var $searchResults = $("#search-results");
        var $searchPriceMin = $("#search-price-min");
        var $searchPriceMax = $("#search-price-max");
        var $searchMake = $("#search-make");
        var $searchModel = $("#search-model");
        var $searchYear = $("#search-year");
        var $searchWhere = $("#search-where");
        var $searchUser = $("#search-user");
        var $searchTransmission = $("#search-transmission");
        var $searchTraction = $("#search-traction");
        var $searchHandicapped = $("#search-handicapped");
        var $searchArmor = $("#search-armor");
        var $facetsToggle = $("#facets-toggle");

        var currentPage;
        var currentSort;
        var pages;

        $(document.documentElement).on("keyup", function (e) {
            if (e.target.nodeName.toLowerCase() !== "body") {
                return;
            }

            if (e.keyCode === 191) {
                // focus on search by pressing slash (/)
                $searchText.focus();
            } else if (e.keyCode === 37 && typeof currentPage === "number" && currentPage > 1) {
                //pagination with left key
                search({page: currentPage - 1, sort: currentSort, scroll: true});
            } else if (e.keyCode === 39 && typeof currentPage === "number" && typeof pages === "number" && pages > currentPage) {
                //pagination with right key
                search({page: currentPage + 1, sort: currentSort, scroll: true});
            }
        });

        $searchResults.on("click", ".sortable a", function (e) {
            var target = e.currentTarget;
            e.preventDefault();

            var sortBy = target.getAttribute("data-sort");
            search({sort: sortBy});
        });

        $searchResults.on("click", ".pagination a", function (e) {
            var target = e.currentTarget;
            e.preventDefault();
            var page = target.getAttribute("data-page");
            var sort = target.getAttribute("data-sort");
            search({page: page, sort: sort, scroll: true});
        });

        var changeViewStyle = function (style) {
            if (style === "table") {
                $($(".results-thumbnail", $searchResults)[0]).addClass("hidden");
            } else {
                $($(".results-table", $searchResults)[0]).addClass("hidden");
            }

            $($(".results-" + style, $searchResults)[0]).removeClass("hidden");

            AppParams.postsViewStyle = style;

            $.ajax({
                url: AppParams.webroot + "/search?posts_view_style=" + style,
                type: "HEAD"
            });
        };

        $searchResults.on("click", ".posts-view-style-table", function (e) {
            changeViewStyle("table");
            e.preventDefault();
        });

        $searchResults.on("click", ".posts-view-style-thumbnail", function (e) {
            changeViewStyle("thumbnail");
            e.preventDefault();
        });

        var removeFilters = function () {
            $searchPostsForm.find(':input').each(function (e) {
                var type = this.type;
                var tag = this.tagName.toLowerCase();

                if (this.name !== "q") {
                    if (type === 'text' || type === 'password' || tag === 'textarea') {
                        this.value = '';
                    } else if (type === 'checkbox' || type === 'radio') {
                        this.checked = false;
                    } else if (tag === 'select') {
                        this.selectedIndex = -1;
                    }
                }
            });

            $('[name="type"][value=""]', $searchPostsForm).prop("checked", true);
        };

        var search = function (data) {
            if (data === undefined) {
                data = {};
            }

            var sort = data.sort || "";
            var page = data.page || 1;
            var scroll = data.scroll || false;

            if (scroll) {
                $body.animate({
                    scrollTop: ($searchResults.offset().top) - 50
                }, 500);
            }

            var formSerialized = $(':input', $searchPostsForm).filter(
                function () {
                    return $(this).val();
                }
            ).serialize();

            var size = 20;
            var from = 1;
            var to;
            var total;

            if (page !== undefined) {

                page = parseInt(page, 10);

                if (isNaN(page)) {
                    page = 1;
                }

                from = ((page - 1) * size) + 1;
            } else {
                page = 1;
            }

            $.ajax({
                data: formSerialized,
                url: AppParams.webroot + "/search-engine?size=" + encodeURIComponent(size) + "&from=" + encodeURIComponent(from) + "&sort=" + encodeURIComponent(sort),
                type: "GET",
                success: function (result) {
                    total = result.hits.total;
                    to = from + size - 1;

                    pages = Math.ceil(total / size);

                    if (to > total) {
                        to = total;
                    }

                    var compiledResults = underscore.template(resultsTemplate);
                    var compiledFacets = underscore.template(facetsTemplate);

                    var viewStyle;

                    if (AppParams.postsViewStyle === "table") {
                        viewStyle = "table";
                    } else {
                        viewStyle = "thumbnail";
                    }

                    currentPage = page;
                    currentSort = sort;

                    var searchParams = searchModel.parseQueryString(formSerialized);

                    if (currentPage !== 1) {
                        searchParams.page = currentPage;
                    }

                    if (sort) {
                        searchParams.sort = sort;
                    }

                    if (searchParams["price-min"]) {
                        searchParams["price-min"] = decodeURIComponent(searchParams["price-min"]).replace(/[^0-9]/g, '');
                    }

                    if (searchParams["price-max"]) {
                        searchParams["price-max"] = decodeURIComponent(searchParams["price-max"]).replace(/[^0-9]/g, '');
                    }

                    if (window.history && window.history.pushState) {
                        var address = AppParams.webroot + "/search";

                        address += "?" + decodeURIComponent($.param(searchParams)).replace(/%2B/g, '+');

                        if (window.location.pathname + window.location.search !== address) {
                            window.history.pushState(null, null, address);
                        }
                    }

                    var searchParamsTotal = underscore.size(searchParams);

                    var facetsHtml = compiledFacets(
                        {
                            pageLink : searchModel.pageLink,
                            formatMoney : searchModel.formatMoney,
                            termListHtmlElements : searchModel.termListHtmlElements,
                            termListHtmlElementsType : searchModel.termListHtmlElementsType,
                            termListHtmlElementsBool : searchModel.termListHtmlElementsBool,
                            formSerialized : formSerialized,
                            transmissionTranslation : searchModel.transmissionTranslation,
                            tractionTranslation : searchModel.tractionTranslation,
                            facets : result.facets,
                            searchParamsTotal : searchParamsTotal,
                            currentQueryStringParams : searchModel.parseQueryString(window.location.search.substr(1).replace(/\+/g, ' '))
                        }
                    );

                    var getFirstPictureAddress = function (pictures, cropOptions) {
                        var picture;
                        if (pictures !== undefined && pictures !== null && pictures[0] !== undefined) {
                            picture = pictures[0];
                        } else {
                            picture = {
                                "picture_id" : AppParams.placeholder
                            };
                        }

                        var cropSubPath = "";
                        if (picture.crop_options) {
                            cropSubPath = picture.crop_options.x + "x" + picture.crop_options.y + ":" +
                                picture.crop_options.x2 + "x" + picture.crop_options.y2 + "/"
                            ;
                        }

                        cropSubPath = cropSubPath + cropOptions;

                        return AppParams.imagesCdn + "/unsafe/" + cropSubPath + picture.picture_id + ".jpg";
                    };

                    if ($facetsToggle.hasClass("hidden")) {
                        $facetsToggle.removeClass("hidden");
                    }

                    $searchResults.html(compiledResults(
                        {
                            facetsHtml : facetsHtml,
                            pageLink : searchModel.pageLink,
                            formatMoney : searchModel.formatMoney,
                            AppParams : AppParams,
                            getFirstPictureAddress : getFirstPictureAddress,
                            result : result,
                            size : size,
                            from : from,
                            to : to,
                            total : total,
                            sort : sort,
                            viewStyle : viewStyle,
                            formSerialized : formSerialized,
                            currentPage : currentPage,
                            pages : pages
                        }
                    ));

                    var $priceInputs = $(".price-inputs", $searchResults);
                    var $priceMinInput = $(".price-min-input", $searchResults);
                    var $priceMaxInput = $(".price-max-input", $searchResults);

                    $priceMinInput.val(searchModel.formatMoney($searchPriceMin.val()));
                    $priceMaxInput.val(searchModel.formatMoney($searchPriceMax.val()));

                    $priceInputs.tooltip();

                    searchModel.maskMoney($priceMinInput);
                    searchModel.maskMoney($priceMaxInput);

                    $priceInputs.on("keyup", function (e) {
                        if (e.keyCode === 13) {
                            var priceMin = $priceMinInput.val().replace(/[^\d]/g, "");
                            var priceMax = $priceMaxInput.val().replace(/[^\d]/g, "");
                            $searchPriceMin.val(priceMin);
                            $searchPriceMax.val(priceMax);
                            search();
                        }
                    });
                }
            });
        };

        var changeSearchTermByUrl = function (urlParams, $termObject, name) {
            if (urlParams[name] !== undefined) {
                $termObject.val(decodeURIComponent(urlParams[name].replace(/\+/gi, " ")));
            } else {
                $termObject.val("");
            }
        };

        var changeSearchTermsByUrlParams = function (urlParams) {
            changeSearchTermByUrl(urlParams, $searchPriceMin, "price-min");
            changeSearchTermByUrl(urlParams, $searchPriceMax, "price-max");

            if (urlParams.type !== undefined) {
                $searchTypesNames.filter('[value="' + underscore.escape(urlParams.type) + '"]').prop("checked", true);
            }

            changeSearchTermByUrl(urlParams, $searchMake, "make");
            changeSearchTermByUrl(urlParams, $searchModel, "model");
            changeSearchTermByUrl(urlParams, $searchYear, "year");
            changeSearchTermByUrl(urlParams, $searchWhere, "where");
            changeSearchTermByUrl(urlParams, $searchUser, "u");
            changeSearchTermByUrl(urlParams, $searchTransmission, "transmission");
            changeSearchTermByUrl(urlParams, $searchTraction, "traction");
            changeSearchTermByUrl(urlParams, $searchHandicapped, "handicapped");
            changeSearchTermByUrl(urlParams, $searchArmor, "armor");
        };

        var urlParams = searchModel.parseQueryString(window.location.search.substr(1).replace(/\+/g, ' '));

        changeSearchTermsByUrlParams(urlParams);

        // if the query is defined on the page load, search by it
        // otherwise, if the search is accessed without a query and not on the index page,
        // focus it, and if the page is in the index page, load the index js file
        if (urlParams.q !== undefined) {
            $searchText.val(decodeURIComponent(urlParams.q.replace(/\+/gi, " ")));
            search({page: urlParams.page || 1, sort: urlParams.sort});
        } else if (window.location.pathname === "/") {
            require(["views/index/index"], function () {
            });
        } else {
            $searchText.focus();
        }

        window.onpopstate = function(event) {
            var urlParams = searchModel.parseQueryString(window.location.search.substr(1).replace(/\+/g, ' '));

            changeSearchTermsByUrlParams(urlParams);

            $searchText.val(decodeURIComponent(urlParams.q.replace(/\+/gi, " ")));
            search({page: urlParams.page || 1, sort: urlParams.sort});
        };

        $searchText.on("change", function (e) {
            setTimeout(function () {
                search();
            }, 200);

        });
        $searchText.on("paste", function (e) {
            setTimeout(function () {
                search();
            }, 200);
        });

        $searchText.on("keyup", function (e) {
            var q = $searchText.val();

            //only go ahead if the keyCode is a "printable character" or erase / delete, return
            var key = e.keyCode;
            if ((key >= 48 && key <= 90) || (key >= 188 && key <= 222) || key === 8 || key === 46 || key === 13) {
                search();
            }
        });

        $searchPostsForm.on("submit", function (e) {
            e.preventDefault();
            removeFilters();
            search();
        });

        $searchResults.on("click", ".remove-filters", function (e) {
            removeFilters();
            search();
        });

        $searchResults.on("click", ".search-facets a", function (e) {
            var target = e.currentTarget;

            e.preventDefault();

            var name = target.getAttribute("data-name");
            var value = target.getAttribute("data-value");

            var $input = $('[name="' + underscore.escape(name) + '"]', $searchPostsForm);

            if (name === "price") {
                $searchPriceMin.val(target.getAttribute("data-price-from"));
                $searchPriceMax.val(target.getAttribute("data-price-to"));

            } else if ($input.attr("type") === "radio") {
                $input.filter('[value="' + underscore.escape(value) + '"]').prop("checked", true);
            } else {
                $input.val(value);
            }

            search();
        });

        $facetsToggle.on("click", function (e) {
            e.preventDefault();
            var $searchFacets = $(".search-facets", $searchResults);
            if ($facetsToggle.hasClass("active")) {
                $searchFacets.addClass("hidden-phone");
            } else {
                $searchFacets.removeClass("hidden-phone");
            }
        });
    }
);
