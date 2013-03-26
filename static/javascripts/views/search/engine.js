/*global define */
/*jshint indent:4 */

define(['AppParams', 'jquery', 'underscore', 'text!templates/search/results.html', 'text!templates/search/facets.html', 'jquery.maskMoney'],
    function (AppParams, $, underscore, resultsTemplate, facetsTemplate) {
        "use strict";

        var parseQueryString = function (queryString) {
            //from http://www.joezimjs.com/javascript/3-ways-to-parse-a-query-string-in-a-url/
            var params = {};
            var queries;
            var temp;
            var i;
            var l;

            // Split into key/value pairs
            queries = queryString.split("&");

            // Convert the array of strings into an object
            for (i = 0, l = queries.length; i < l; i = i + 1) {
                temp = queries[i].split('=');
                params[temp[0]] = temp[1];
            }

            return params;
        };

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

        var maskMoney = function ($element) {
            $element.maskMoney(
                {
                    symbol: 'R$ ',
                    showSymbol: true,
                    symbolStay: true,
                    thousands: '.',
                    decimal: ',',
                    defaultZero: false
                }
            );
        };

        var urlParts = (function () {
            var vars = {};
            window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
                if (vars[key] !== undefined) {
                    vars[key].push(value.toLowerCase());
                } else {
                    vars[key] = value.toLowerCase();
                }
            });

            return vars;
        } ());

        var pageLink = function (formSerialized, page, pageSort) {
            if (formSerialized === "") {
                if (page === 1) {
                    if (pageSort) {
                        return "?sort=" + pageSort;
                    } else {
                        return "";
                    }
                } else {
                    if (pageSort) {
                        return "?page=" + page + "&sort=" + pageSort;
                    } else {
                        return "?page=" + page;
                    }
                }
            } else {
                if (page === 1) {
                    if (pageSort) {
                        return "?" + formSerialized + "&sort=" + pageSort;
                    } else {
                        return "?" + formSerialized;
                    }
                } else {
                    if (pageSort) {
                        return "?" + formSerialized + "&page=" + page + "&sort=" + pageSort;
                    } else {
                        return "?" + formSerialized + "&page=" + page;
                    }
                }
            }
        };

        var formatMoney = function (money) {
            var $foo = $('<input type="text">');
            maskMoney($foo);
            $foo.val(money);
            $foo.trigger("mask");
            return $foo.val();
        };

        var termListHtmlElementsType = function (terms, formSerialized, currentQueryStringParams) {
            var jsonFormSerialized = parseQueryString(formSerialized.replace(/\+/g, ' '));
            var types = {
                car : "carro",
                motorcycle : "motocicleta",
                boat : "embarcação"
            };

            var content = "";

            for (var termPos = 0, termLength = terms.length; termLength > termPos; termPos = termPos + 1) {
                jsonFormSerialized.type = underscore.escape(terms[termPos].term);

                content += "<li>";

                if (underscore.isEqual(currentQueryStringParams, jsonFormSerialized)) {
                    delete jsonFormSerialized.type;
                    var escapedUrlRemove = "?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                    content += '<span class="label label-inverse">' +
                        underscore.escape(types[terms[termPos].term]) +
                        " (" + underscore.escape(terms[termPos].count) + ")" +
                        ' <a href="' + escapedUrlRemove + '" data-name="type" ' +
                        'data-value=""><i class="icon-remove icon-white"></i><span class="hidden"> remover</span></a>' +
                    "</span>";
                } else {
                    var escapedUrl = "?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                    content += '<a href="' + escapedUrl + '" data-name="type" ' +
                        'data-value="' + underscore.escape(terms[termPos].term) + '">' +
                        underscore.escape(types[terms[termPos].term]) +
                        "</a> (" + underscore.escape(terms[termPos].count) + ")";
                }

                content += "</li>";
            }

            return content;
        };

        var termListHtmlElements = function (
            termName,
            terms,
            formSerialized,
            currentQueryStringParams,
            translationObject
        ) {
            var jsonFormSerialized = parseQueryString(formSerialized.replace(/\+/g, ' '));
            var content = "";

            for (var termPos = 0, termLength = terms.length; termLength > termPos; termPos = termPos + 1) {
                jsonFormSerialized[termName] = underscore.escape(terms[termPos].term);

                var escapedUrl = "?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                content += "<li>";

                var value = terms[termPos].term;

                if (translationObject !== undefined && translationObject[value] !== undefined) {
                    value = translationObject[value];
                }

                if (underscore.isEqual(currentQueryStringParams, jsonFormSerialized)) {
                    delete jsonFormSerialized[termName];
                    var escapedUrlRemove = "?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                    content += '<span class="label label-inverse">' +
                        underscore.escape(value) +
                        " (" + underscore.escape(terms[termPos].count) + ')' +
                        ' <a href="' + escapedUrlRemove + '" data-name="' +
                        underscore.escape(termName) + '" ' +
                        'data-value=""><i class="icon-remove icon-white"></i><span class="hidden"> remover</span></a>' +
                        '</span>';
                } else {
                    content += '<a href="' + escapedUrl + '" data-name="' +
                        underscore.escape(termName) + '" ' +
                        'data-value="' + underscore.escape(terms[termPos].term) + '">' +
                        underscore.escape(value) +
                        "</a> (" + underscore.escape(terms[termPos].count) + ")";
                }

                content += "</li>";
            }

            return content;
        };

        var termListHtmlElementsBool = function (
            termName,
            terms,
            formSerialized,
            currentQueryStringParams,
            value
            ) {
            var jsonFormSerialized = parseQueryString(formSerialized.replace(/\+/g, ' '));
            var content = "";

            for (var termPos = 0, termLength = terms.length; termLength > termPos; termPos = termPos + 1) {
                jsonFormSerialized[termName] = "1";

                var escapedUrl = "?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                if (terms[termPos].term !== "T") {
                    continue;
                }

                content += "<li>";

                if (underscore.isEqual(currentQueryStringParams, jsonFormSerialized)) {
                    delete jsonFormSerialized[termName];
                    var escapedUrlRemove = "?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                    content += '<span class="label label-inverse">' +
                        underscore.escape(value) +
                        " (" + underscore.escape(terms[termPos].count) + ')' +
                        ' <a href="' + escapedUrlRemove + '" data-name="' +
                        underscore.escape(termName) + '" ' +
                        'data-value=""><i class="icon-remove icon-white"></i><span class="hidden"> remover</span></a>' +
                        '</span>';
                } else {
                    content += '<a href="' + escapedUrl + '" data-name="' +
                        underscore.escape(termName) + '" ' +
                        'data-value="1">' +
                        underscore.escape(value) +
                        "</a> (" + underscore.escape(terms[termPos].count) + ")";
                }

                content += "</li>";
            }

            return content;
        };

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

        var transmissionTranslation = {
            "manual" : "manual",
            "automatic" : "automático",
            "other" : "outra"
        };

        var tractionTranslation = {
            "front" : "frontal",
            "rear" : "traseira",
            "4x4" : "4x4"
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

                    var searchParams = parseQueryString(formSerialized);

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

                        window.history.pushState(null, null, address);
                    }

                    var searchParamsTotal = underscore.size(searchParams);

                    var facetsHtml = compiledFacets(
                        {
                            pageLink : pageLink,
                            formatMoney : formatMoney,
                            termListHtmlElements : termListHtmlElements,
                            termListHtmlElementsType : termListHtmlElementsType,
                            termListHtmlElementsBool : termListHtmlElementsBool,
                            formSerialized : formSerialized,
                            transmissionTranslation : transmissionTranslation,
                            tractionTranslation : tractionTranslation,
                            facets : result.facets,
                            searchParamsTotal : searchParamsTotal,
                            currentQueryStringParams : parseQueryString(window.location.search.substr(1).replace(/\+/g, ' '))
                        }
                    );

                    $searchResults.html(compiledResults(
                        {
                            facetsHtml : facetsHtml,
                            pageLink : pageLink,
                            formatMoney : formatMoney,
                            AppParams : AppParams,
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

                    $priceMinInput.val(formatMoney($searchPriceMin.val()));
                    $priceMaxInput.val(formatMoney($searchPriceMax.val()));

                    $priceInputs.tooltip();

                    maskMoney($priceMinInput);
                    maskMoney($priceMaxInput);

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

        var changeSearchTermByUrl = function ($termObject, name) {
            if (urlParts[name] !== undefined) {
                $termObject.val(decodeURIComponent(urlParts[name].replace(/\+/gi, " ")));
            }
        };

        changeSearchTermByUrl($searchPriceMin, "price-min");
        changeSearchTermByUrl($searchPriceMax, "price-max");

        if (urlParts.type !== undefined) {
            $searchTypesNames.filter('[value="' + underscore.escape(urlParts.type) + '"]').prop("checked", true);
        }

        changeSearchTermByUrl($searchMake, "make");
        changeSearchTermByUrl($searchModel, "model");
        changeSearchTermByUrl($searchYear, "year");
        changeSearchTermByUrl($searchWhere, "where");
        changeSearchTermByUrl($searchUser, "u");
        changeSearchTermByUrl($searchTransmission, "transmission");
        changeSearchTermByUrl($searchTraction, "traction");
        changeSearchTermByUrl($searchHandicapped, "handicapped");
        changeSearchTermByUrl($searchArmor, "armor");

        if (urlParts.q !== undefined) {
            $searchText.val(decodeURIComponent(urlParts.q.replace(/\+/gi, " ")));
            search({page: urlParts.page || 1, sort: urlParts.sort});
        } else {
            $searchText.focus();
        }

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
