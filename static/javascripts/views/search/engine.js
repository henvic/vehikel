/*global define, require */
/*jslint browser: true */

define(
    [
        'AppParams',
        'jquery',
        'underscore',
        'models/search',
        'text!templates/search/results.html',
        'text!templates/search/facets.html'
    ],
    function (AppParams, $, underscore, searchModel, resultsTemplate, facetsTemplate) {
        'use strict';

        var $body = $('body'),
            $searchPostsForm = $('#search-posts-form'),
            $searchText = $('#search-text'),
            $searchGo = $('#search-go'),
            $searchTypesNames = $('[name="type"]', $searchPostsForm),
            $searchResults = $('#search-results'),
            $searchPriceMin = $('#search-price-min'),
            $searchPriceMax = $('#search-price-max'),
            $searchMake = $('#search-make'),
            $searchModel = $('#search-model'),
            $searchYear = $('#search-year'),
            $searchWhere = $('#search-where'),
            $searchUser = $('#search-user'),
            $searchTransmission = $('#search-transmission'),
            $searchTraction = $('#search-traction'),
            $searchHandicapped = $('#search-handicapped'),
            $searchCollection = $('#search-collection'),
            $searchArmor = $('#search-armor'),
            $facetsToggle = $('#facets-toggle'),
            currentPage,
            currentSort,
            pages,
            changeViewStyle,
            removeFilters,
            search,
            compiledResults = underscore.template(resultsTemplate),
            compiledFacets = underscore.template(facetsTemplate),
            changeSearchTermByUrl,
            changeSearchTermsByUrlParams,
            urlParams;

        $(document.documentElement).on('keyup', function (e) {
            if (e.target.nodeName.toLowerCase() !== 'body') {
                return;
            }

            if (e.keyCode === 191) {
                // focus on search by pressing slash (/)
                $searchText.focus();
            } else if (e.keyCode === 37 && typeof currentPage === 'number' && currentPage > 1) {
                //pagination with left key
                search({page: currentPage - 1, sort: currentSort, scroll: true});
            } else if (e.keyCode === 39 && typeof currentPage === 'number' && typeof pages === 'number' && pages > currentPage) {
                //pagination with right key
                search({page: currentPage + 1, sort: currentSort, scroll: true});
            }
        });

        $searchResults.on('click', '.sortable a', function (e) {
            var target = e.currentTarget,
                sortBy = target.getAttribute('data-sort');

            e.preventDefault();
            search({sort: sortBy});
        });

        $searchResults.on('click', '.pagination a', function (e) {
            var target = e.currentTarget,
                page = target.getAttribute('data-page'),
                sort = target.getAttribute('data-sort');

            e.preventDefault();
            search({page: page, sort: sort, scroll: true});
        });

        changeViewStyle = function (style) {
            if (style === 'table') {
                $($('.results-thumbnail', $searchResults)[0]).addClass('none');
            } else {
                $($('.results-table', $searchResults)[0]).addClass('none');
            }

            $($('.results-' + style, $searchResults)[0]).removeClass('none');

            AppParams.postsViewStyle = style;

            $.ajax({
                url: AppParams.webroot + '/search?posts_view_style=' + style,
                type: 'HEAD'
            });
        };

        $searchResults.on('click', '.posts-view-style-table', function (e) {
            changeViewStyle('table');
            e.preventDefault();
        });

        $searchResults.on('click', '.posts-view-style-thumbnail', function (e) {
            changeViewStyle('thumbnail');
            e.preventDefault();
        });

        removeFilters = function () {
            $searchPostsForm.find(':input').each(function () {
                var type = this.type,
                    tag = this.tagName.toLowerCase();

                if (this.name !== 'q') {
                    if (type === 'text' || type === 'password' || tag === 'textarea') {
                        this.value = '';
                    } else if (type === 'checkbox' || type === 'radio') {
                        this.checked = false;
                    } else if (tag === 'select') {
                        this.selectedIndex = -1;
                    }
                }
            });

            $('[name="type"][value=""]', $searchPostsForm).prop('checked', true);
            $('[name="persist-username"][value=""]', $searchPostsForm).prop('checked', true);
        };

        search = function (data) {
            var persistUsernameValue,
                sort,
                page,
                scroll,
                formSerialized,
                size,
                from,
                to,
                total;

            persistUsernameValue = $('[name=persist-username]:checked').val();

            if (persistUsernameValue) {
                $searchUser.val(persistUsernameValue);
            }

            if (data === undefined) {
                data = {};
            }

            sort = data.sort || '';
            page = data.page || 1;
            scroll = data.scroll || false;

            if (scroll) {
                $body.animate({
                    scrollTop: ($searchResults.offset().top) - 50
                }, 500);
            }

            formSerialized = $(':input', $searchPostsForm).filter(
                function () {
                    return $(this).val();
                }
            ).serialize();

            size = 20;
            from = 1;

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
                url: AppParams.webroot + '/search-engine?size=' + encodeURIComponent(size) + '&from=' + encodeURIComponent(from) + '&sort=' + encodeURIComponent(sort),
                type: 'GET',
                success: function (result) {
                    var viewStyle,
                        searchParams,
                        oldSearchParams,
                        searchParamsEqual,
                        address,
                        part,
                        searchParamsTotal,
                        facetsHtml,
                        getFirstPictureAddress,
                        $priceInputs,
                        $priceMinInput,
                        $priceMaxInput;

                    total = result.hits.total;
                    to = from + size - 1;

                    pages = Math.ceil(total / size);

                    if (to > total) {
                        to = total;
                    }

                    if (AppParams.postsViewStyle === 'table') {
                        viewStyle = 'table';
                    } else {
                        viewStyle = 'thumbnail';
                    }

                    currentPage = page;
                    currentSort = sort;

                    searchParams = searchModel.parseQueryString(formSerialized);

                    if (currentPage !== 1) {
                        searchParams.page = currentPage;
                    }

                    if (sort) {
                        searchParams.sort = sort;
                    }

                    if (searchParams['price-min']) {
                        searchParams['price-min'] = decodeURIComponent(searchParams['price-min']).match(/[\d]/g).join('');
                    }

                    if (searchParams['price-max']) {
                        searchParams['price-max'] = decodeURIComponent(searchParams['price-max']).match(/[\d]/g).join('');
                    }

                    oldSearchParams = searchModel.parseLocationQueryString(window);

                    searchParamsEqual = underscore.isEqual(searchParams, oldSearchParams);

                    if ((window.history && window.history.pushState && !searchParamsEqual) ||
                            window.location.pathname !== AppParams.webroot + '/search') {
                        address = AppParams.webroot + '/search';

                        part = $.param(searchParams);

                        if (part === '=') {
                            part = 'q=';
                        }

                        address += '?' + decodeURIComponent(part).replace(/%2B/g, '+');

                        window.history.pushState(null, null, address);
                    }

                    searchParamsTotal = underscore.size(searchParams);

                    facetsHtml = compiledFacets(
                        {
                            pageLink: searchModel.pageLink,
                            formatMoney: searchModel.formatMoney,
                            termListHtmlElements: searchModel.termListHtmlElements,
                            termListHtmlElementsType: searchModel.termListHtmlElementsType,
                            termListHtmlElementsBool: searchModel.termListHtmlElementsBool,
                            formSerialized: formSerialized,
                            transmissionTranslation: searchModel.transmissionTranslation,
                            tractionTranslation: searchModel.tractionTranslation,
                            facets: result.facets,
                            searchParamsTotal: searchParamsTotal,
                            currentQueryStringParams: searchModel.parseLocationQueryString()
                        }
                    );

                    getFirstPictureAddress = function (pictures, cropOptions) {
                        var picture,
                            cropSubPath;
                        if (pictures !== undefined && pictures !== null && pictures[0] !== undefined) {
                            picture = pictures[0];
                        } else {
                            picture = {
                                'picture_id' : AppParams.placeholder
                            };
                        }

                        cropSubPath = '';
                        if (picture.crop_options) {
                            cropSubPath = picture.crop_options.x + 'x' + picture.crop_options.y + ':' +
                                picture.crop_options.x2 + 'x' + picture.crop_options.y2 + '/';
                        }

                        cropSubPath = cropSubPath + cropOptions;

                        return AppParams.imagesCdn + '/unsafe/' + cropSubPath + picture.picture_id + '.jpg';
                    };

                    if ($facetsToggle.hasClass('hidden')) {
                        $facetsToggle.removeClass('hidden');
                    }

                    $searchResults.html(compiledResults(
                        {
                            facetsHtml: facetsHtml,
                            pageLink: searchModel.pageLink,
                            formatMoney: searchModel.formatMoney,
                            AppParams: AppParams,
                            getFirstPictureAddress: getFirstPictureAddress,
                            result: result,
                            size: size,
                            from: from,
                            to: to,
                            total: total,
                            sort: sort,
                            viewStyle: viewStyle,
                            formSerialized: formSerialized,
                            currentPage: currentPage,
                            pages: pages
                        }
                    ));

                    $priceInputs = $('.price-inputs', $searchResults);
                    $priceMinInput = $('.price-min-input', $searchResults);
                    $priceMaxInput = $('.price-max-input', $searchResults);

                    $priceMinInput.val(searchModel.formatMoney($searchPriceMin.val()));
                    $priceMaxInput.val(searchModel.formatMoney($searchPriceMax.val()));

                    $priceInputs.tooltip();

                    searchModel.maskMoney($priceMinInput);
                    searchModel.maskMoney($priceMaxInput);

                    $priceInputs.on('keyup', function (e) {
                        if (e.keyCode === 13) {
                            var priceMin,
                                priceMax;

                            priceMin = $priceMinInput.val().match(/[\d]/g);
                            priceMax = $priceMaxInput.val().match(/[\d]/g);

                            if (priceMin === null) {
                                priceMin = [];
                            }

                            if (priceMax === null) {
                                priceMax = [];
                            }

                            priceMin = priceMin.join('');
                            priceMax = priceMax.join('');

                            $searchPriceMin.val(priceMin);
                            $searchPriceMax.val(priceMax);
                            search();
                        }
                    });
                }
            });
        };

        changeSearchTermByUrl = function (urlParams, $termObject, name) {
            if (urlParams[name] !== undefined) {
                $termObject.val(decodeURIComponent(urlParams[name].replace(/\+/gi, ' ')));
            } else {
                $termObject.val('');
            }
        };

        changeSearchTermsByUrlParams = function (urlParams) {
            changeSearchTermByUrl(urlParams, $searchPriceMin, 'price-min');
            changeSearchTermByUrl(urlParams, $searchPriceMax, 'price-max');

            if (urlParams.type !== undefined) {
                $searchTypesNames.filter('[value="' + underscore.escape(urlParams.type) + '"]').prop('checked', true);
            }

            changeSearchTermByUrl(urlParams, $searchMake, 'make');
            changeSearchTermByUrl(urlParams, $searchModel, 'model');
            changeSearchTermByUrl(urlParams, $searchYear, 'year');
            changeSearchTermByUrl(urlParams, $searchWhere, 'where');
            changeSearchTermByUrl(urlParams, $searchUser, 'u');
            changeSearchTermByUrl(urlParams, $searchTransmission, 'transmission');
            changeSearchTermByUrl(urlParams, $searchTraction, 'traction');
            changeSearchTermByUrl(urlParams, $searchHandicapped, 'handicapped');
            changeSearchTermByUrl(urlParams, $searchCollection, 'collection');
            changeSearchTermByUrl(urlParams, $searchArmor, 'armor');
        };

        urlParams = searchModel.parseLocationQueryString();

        changeSearchTermsByUrlParams(urlParams);

        // if the query is defined on the page load, search by it
        // otherwise, if the search is accessed without a query and not on the index page,
        // focus it, and if the page is in the index page, load the index js file
        if (window.location.pathname === '/') {
            require(['views/index/index']);
        } else if (urlParams.q !== undefined) {
            $searchText.val(decodeURIComponent(urlParams.q.replace(/\+/gi, ' ')));
            search({page: urlParams.page || 1, sort: urlParams.sort});
        } else {
            $searchText.focus();
        }

        window.onpopstate = function () {
            if (window.location.pathname === AppParams.webroot + '/') {
                return;
            }

            var urlParams = searchModel.parseLocationQueryString();

            changeSearchTermsByUrlParams(urlParams);

            if (urlParams.q !== undefined) {
                $searchText.val(decodeURIComponent(urlParams.q.replace(/\+/gi, ' ')));
                search({page: urlParams.page || 1, sort: urlParams.sort});
            }
        };

        $searchText.on('change', function () {
            setTimeout(function () {
                search();
            }, 200);

        });
        $searchText.on('paste', function () {
            setTimeout(function () {
                search();
            }, 200);
        });

        $searchText.on('keyup', function (e) {
            //only go ahead if the keyCode is a "printable character" or erase / delete, return
            var key = e.keyCode;
            if ((key >= 48 && key <= 90) || (key >= 188 && key <= 222) || key === 8 || key === 46 || key === 13) {
                search();
            }
        });

        $searchGo.on('click', function (e) {
            e.preventDefault();
            search();
        });

        $searchResults.on('click', '.remove-filters', function () {
            removeFilters();
            search();
        });

        $searchResults.on('click', '.search-facets a', function (e) {
            var target,
                name,
                value,
                $input;

            e.preventDefault();

            target = e.currentTarget;
            name = target.getAttribute('data-name');
            value = target.getAttribute('data-value');
            $input = $('[name="' + underscore.escape(name) + '"]', $searchPostsForm);

            if (name === 'price') {
                $searchPriceMin.val(target.getAttribute('data-price-from'));
                $searchPriceMax.val(target.getAttribute('data-price-to'));

            } else if ($input.attr('type') === 'radio') {
                $input.filter('[value="' + underscore.escape(value) + '"]').prop('checked', true);
            } else {
                if (name === 'u') {
                    $('[name="persist-username"][value=""]', $searchPostsForm).prop('checked', true);
                }

                $input.val(value);
            }

            search();
        });

        $facetsToggle.on('click', function (e) {
            e.preventDefault();
            var $searchFacets = $('.search-facets', $searchResults);
            if ($facetsToggle.hasClass('active')) {
                $searchFacets.addClass('hidden-phone');
            } else {
                $searchFacets.removeClass('hidden-phone');
            }
        });
    }
);
