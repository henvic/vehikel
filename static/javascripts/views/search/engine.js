/*global define */
/*jshint indent:4 */

define(['AppParams', 'jquery', 'underscore', 'text!templates/search/results.html', 'text!templates/search/facets.html', 'jquery.maskMoney'],
    function (AppParams, $, underscore, resultsTemplate, facetsTemplate) {
        "use strict";

        var $body = $("body");
        var $postsViewStyle = $("#posts-view-style");
        var $postsViewStyleThumbnail = $("#posts-view-style-thumbnail");
        var $postsViewStyleTable = $("#posts-view-style-table");
        var $searchPostsForm = $("#search-posts-form");
        var $searchText = $("#search-text");
        var $searchTextAutocomplete = $("#search-text-autocomplete");
        var $searchTypes = $("#search-types");
        var $searchTypesNames = $('input[name="type"]', $searchPostsForm);
        var $searchResults = $("#search-results");
        var $searchPrices = $("#search-prices");
        var $searchPriceMin = $("#search-price-min");
        var $searchPriceMax = $("#search-price-max");
        var $searchButton = $("#search-button");

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

        maskMoney($searchPriceMin);
        maskMoney($searchPriceMax);

        var openSearch = function () {
            $searchPrices.removeClass("hidden");
            $searchTypes.removeClass("hidden");
            $searchButton.removeClass("hidden");
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

                    var compiled = underscore.template(resultsTemplate);

                    var viewStyle = $(".active", $postsViewStyle).data("view-style");

                    currentPage = page;
                    currentSort = sort;

                    if (window.history && window.history.pushState) {
                        var address = AppParams.webroot + "/search";

                        var q = $searchText.val();

                        if (! q) {
                            if (formSerialized) {
                                formSerialized = "q=&" + formSerialized;
                            } else {
                                formSerialized = "q=";
                            }
                        }

                        address = address + "?" + formSerialized;

                        if (currentPage !== 1) {
                            address = address + "&page=" + currentPage;
                        }

                        if (sort) {
                            address = address + "&sort=" + encodeURIComponent(sort);
                        }

                        window.history.pushState(null, null, address);
                    }

                    $searchResults.html(compiled(
                        {
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
            if ($.inArray(element.value, urlParts["type"]) !== -1) {
                element.checked = true;
            }
        });

        if (urlParts.q !== undefined) {
            $searchText.val(decodeURIComponent(urlParts.q.replace(/\+/gi, " ")));
            search({page: urlParts.page || 1, sort: urlParts.sort});
            openSearch();
        } else {
            $searchText.focus();
            $searchText.one("keyup", function (e) {
                openSearch();
            });
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
    }
);
