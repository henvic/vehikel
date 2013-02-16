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
            for (i = 0, l = queries.length; i < l; i++) {
                temp = queries[i].split('=');
                params[temp[0]] = temp[1];
            }

            return params;
        };

        var $body = $("body");
        var $postsViewStyle = $("#posts-view-style");
        var $postsViewStyleThumbnail = $("#posts-view-style-thumbnail");
        var $postsViewStyleTable = $("#posts-view-style-table");
        var $searchPostsForm = $("#search-posts-form");
        var $searchText = $("#search-text");
        var $searchTextAutocomplete = $("#search-text-autocomplete");
        var $searchTypesNames = $('#search-types [name="type"]', $searchPostsForm);
        var $searchResults = $("#search-results");
        var $searchPriceMin = $("#search-price-min");
        var $searchPriceMax = $("#search-price-max");
        var $facetsToggle = $("#facets-toggle");

        var currentPage;
        var currentSort;
        var pages;
        var lastSuggestions = [];
        var allPastSuggestions = [];

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
                    if (typeof vars[key] === "string") {
                        vars[key] = [vars[key]];
                        vars[key].push(value);
                    } else {
                        vars[key].push(value);
                    }
                } else {
                    vars[key] = value;
                }
            });

            if (vars.type !== undefined && typeof vars.type === "string") {
                vars.type = [vars.type];
            }

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

            for (var termPos = 0, termLength = terms.length; termLength > termPos; termPos++) {
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

        var termListHtmlElementsPrice = function (terms, formSerialized, currentQueryStringParams) {
            var jsonFormSerialized = parseQueryString(formSerialized.replace(/\+/g, ' '));
            var content = "";

            for (var termPos = 0, termLength = terms.length; termLength > termPos; termPos++) {
                var price = terms[termPos];
                var from = price.from || '';
                var to = price.to || '';

                if (from) {
                    jsonFormSerialized["price-min"] = from.toString();
                } else {
                    delete jsonFormSerialized["price-min"];
                }

                if (to) {
                    jsonFormSerialized["price-max"] = to.toString();
                } else {
                    delete jsonFormSerialized["price-max"];
                }

                if (price.count) {
                    var escapedUrl = "?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                    var pricing = "";

                    if (! from) {
                        pricing += "até " + underscore.escape(formatMoney(to));
                    } else if (!to) {
                        pricing += underscore.escape(formatMoney(from)) + " +";
                    } else {
                        pricing += underscore.escape(formatMoney(from)) + " a " +
                            underscore.escape(formatMoney(to));
                    }

                    content += "<li>";

                    if (underscore.isEqual(currentQueryStringParams, jsonFormSerialized)) {
                        delete jsonFormSerialized["price-min"];
                        delete jsonFormSerialized["price-max"];
                        var escapedUrlRemove = "?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                        content += '<span class="label label-inverse">' +
                            pricing + ' (' + underscore.escape(price.count) + ')' +
                            ' <a href="' + escapedUrlRemove + '" ' +
                            'data-name="price" data-price-from="" data-price-to="">' +
                            '<i class="icon-remove icon-white"></i><span class="hidden"> remover</span></a>';

                        content += '</span>';
                    } else {
                        content += '<a ' +
                            'href="' + escapedUrl + '" data-name="price"' +
                            'data-price-from="' + underscore.escape(from) + '"' +
                            'data-price-to="' + underscore.escape(to) + '">' +
                            pricing +
                            "</a> (" + underscore.escape(price.count) + ")";
                    }

                    content += "</li>";
                }
            }

            return content;
        };

        var termListHtmlElements = function (termName, terms, formSerialized, currentQueryStringParams) {
            var jsonFormSerialized = parseQueryString(formSerialized.replace(/\+/g, ' '));
            var content = "";

            for (var termPos = 0, termLength = terms.length; termLength > termPos; termPos++) {
                jsonFormSerialized[termName] = underscore.escape(terms[termPos].term);

                var escapedUrl = "?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                content += "<li>";

                if (underscore.isEqual(currentQueryStringParams, jsonFormSerialized)) {
                    delete jsonFormSerialized[termName];
                    var escapedUrlRemove = "?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                    content += '<span class="label label-inverse">' +
                        underscore.escape(terms[termPos].term) +
                        " (" + underscore.escape(terms[termPos].count) + ')' +
                        ' <a href="' + escapedUrlRemove + '" data-name="' +
                        underscore.escape(termName) + '" ' +
                        'data-value=""><i class="icon-remove icon-white"></i><span class="hidden"> remover</span></a>' +
                        '</span>';
                } else {
                    content += '<a href="' + escapedUrl + '" data-name="' +
                        underscore.escape(termName) + '" ' +
                        'data-value="' + underscore.escape(terms[termPos].term) + '">' +
                        underscore.escape(terms[termPos].term) +
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

            $.ajax({
                url: AppParams.webroot + "/search?posts_view_style=" + style,
                type: "HEAD"
            });
        };

        $postsViewStyleTable.on("click", function (e) {
            changeViewStyle("table");
            e.preventDefault();
        });

        $postsViewStyleThumbnail.on("click", function (e) {
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

            $('[name="type"][value=""]', $searchPostsForm).attr("checked", "checked");
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

            var formSerialized = $(':input[value!=""]', $searchPostsForm).serialize();

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
                url: AppParams.webroot + "search-engine?size=" + encodeURIComponent(size) + "&from=" + encodeURIComponent(from) + "&sort=" + encodeURIComponent(sort),
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

                    var viewStyle = $(".active", $postsViewStyle).data("view-style");

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

                        address += "?" + $.param(searchParams).replace(/%2B/g, '+');

                        window.history.pushState(null, null, address);
                    }

                    var searchParamsTotal = underscore.size(searchParams);

                    var facetsHtml = compiledFacets(
                        {
                            pageLink : pageLink,
                            formatMoney : formatMoney,
                            termListHtmlElements : termListHtmlElements,
                            termListHtmlElementsType : termListHtmlElementsType,
                            termListHtmlElementsPrice : termListHtmlElementsPrice,
                            formSerialized : formSerialized,
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
                }
            });
        };

        $searchText.typeahead({
            source : function (query, process) {
                var formSerialized = $(':input[value!=""]', $searchPostsForm).serialize();
                $.ajax({
                    data: formSerialized,
                    url: AppParams.webroot + "search-engine?suggestion=true",
                    type: "GET",
                    success: function (result) {
                        lastSuggestions = result;
                        process(result);

                        //add all the suggestions to a array to cache them
                        for (var eachSuggestionPosition in lastSuggestions) {
                            if (allPastSuggestions.indexOf(lastSuggestions[eachSuggestionPosition]) === -1) {
                                allPastSuggestions.push(lastSuggestions[eachSuggestionPosition]);
                            }
                        }

                        if (result[0] !== undefined) {
                            var q = $searchText.val();
                            var qAutocomplete = "";

                            if (result[0].indexOf(q.toLowerCase()) !== -1) {
                                qAutocomplete = q.substr(0, result[0].length) + (result[0]).substr(q.length);
                            }

                            $searchTextAutocomplete.val(qAutocomplete);
                        }
                    }
                });
            },
            items : 3
        });

        if (urlParts["price-min"] !== undefined) {
            $searchPriceMin.val(decodeURIComponent(urlParts["price-min"].replace(/\+/gi, " ")));
        }

        if (urlParts["price-max"] !== undefined) {
            $searchPriceMax.val(decodeURIComponent(urlParts["price-max"].replace(/\+/gi, " ")));
        }

        $searchTypesNames.each(function (pos, element) {
            if ($.inArray(element.value, urlParts.type) !== -1) {
                element.checked = true;
            }
        });

        if (urlParts.q !== undefined) {
            $searchText.val(decodeURIComponent(urlParts.q.replace(/\+/gi, " ")));
            search({page: urlParts.page || 1, sort: urlParts.sort});
        } else {
            $searchText.focus();
        }

        $searchTypesNames.on("change", function (e) {
            search();
        });

        $searchText.on("change", function (e) {
            $searchTextAutocomplete.val("");
            search();
        });

        $searchText.on("keyup", function (e) {
            var q = $searchText.val();
            var qAutocomplete = $searchTextAutocomplete.val();

            if (q === "" || qAutocomplete.indexOf(q) < 0) {
                $searchTextAutocomplete.val("");
            }

            //only go ahead if the keyCode is a "printable character" or erase / delete
            var key = e.keyCode;
            if ((key >= 48 && key <= 90) || (key >= 188 && key <= 222) || key === 8 || key === 46) {
                if (allPastSuggestions.indexOf(q.toLowerCase()) !== -1) {
                    search();
                }
            }
        });

        $searchPostsForm.on("submit", function (e) {
            e.preventDefault();
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
                $input.filter('[value="' + underscore.escape(value) + '"]').attr("checked", true);
            } else {
                $input.val(value);
            }

            search();
        });

        $facetsToggle.on("click", function (e) {
            var $searchFacets = $(".search-facets", $searchResults);
            if ($facetsToggle.hasClass("active")) {
                $searchFacets.addClass("hidden-phone");
            } else {
                $searchFacets.removeClass("hidden-phone");
            }
        });
    }
);
