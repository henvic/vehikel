/*global define */
/*jslint browser: true */

define(['AppParams', 'jquery', 'underscore'],
    function (AppParams, $, underscore) {
        'use strict';

        //the #search-results is used both on the search page as well as on the user posts pages
        var $searchPostsForm = ('#search-posts-form'),
            $searchResults = $('#search-results'),
            $searchText = $('#search-text'),
            $searchTips = $('#search-tips'),
            hideSearchTips,
            loadSearch,
            autoComplete = [],
            autoCompletePos = 0,
            autoCompleteLength = 0,
            lastAutoCompleteXhr;

        //change the u input field w/ the username of the given user
        //on the persist-username checkbox, when the checkbox selection is changed
        $('[name=persist-username]', $searchPostsForm).on('change', function () {
            $('[name=u]', $searchPostsForm).val(this.value);
        });

        $searchResults.on('click', '.posts-table-view tr', function (e) {
            var link = e.currentTarget.getAttribute('data-link'),
                $target = $(e.target);

            if (e.target.tagName.toLowerCase() === 'a' || $target.closest('button')[0] !== undefined) {
                return;
            }

            e.preventDefault();
            window.location = link;
        });

        hideSearchTips = function () {
            $searchTips.html('').addClass('hidden');
        };

        loadSearch = function () {
            var q,
                url,
                persistUsernameValue;

            q = $searchText.val();
            url = AppParams.webroot + '/search?q=' + encodeURIComponent(q);
            persistUsernameValue = $('[name=persist-username]:checked').val();

            if (persistUsernameValue) {
                url += '&u=' + encodeURIComponent(persistUsernameValue);
            }

            if (AppParams.route !== 'search/engine') {
                window.location = url;
            } else {
                hideSearchTips();
            }
        };

        $searchTips.on('mouseenter', 'li', function () {
            $('li', $searchTips).removeClass('active');
            $(this).addClass('active');
        });

        $searchTips.on('mouseleave', 'li', function () {
            $('li', $searchTips).removeClass('active');
        });

        $searchTips.on('click', 'li', function () {
            $searchText.val(this.innerHTML);
            loadSearch();
        });

        $searchText.on('blur', function () {
            setTimeout(hideSearchTips, 300);
        });

        // prevent the cursor from moving when the up and down arrows are used
        $searchText.on('keydown', function (e) {
            if (e.keyCode === 38 || e.keyCode === 40) {
                e.preventDefault();
            }
        });

        $searchText.on('keyup', function (e) {
            var key = e.keyCode,
                q = this.value,
                isUp,
                offset,
                requestData,
                persistUsernameValue;

            if (q === '') {
                hideSearchTips();
            } else if (key === 13) {
                loadSearch();
            } else if (key === 27) {
                hideSearchTips();
            } else if (key === 38 || key === 40) { //if key is up or down
                isUp = (key === 38);

                if (autoCompleteLength > 1) {
                    if (isUp) {
                        if (autoCompletePos === 0) {
                            autoCompletePos = autoCompleteLength - 1;
                        } else {
                            autoCompletePos -= 1;
                        }

                        offset = autoCompletePos;
                    } else {
                        if (autoCompletePos === autoCompleteLength) {
                            autoCompletePos = 0;
                        }

                        autoCompletePos += 1;

                        offset = autoCompletePos - 1;
                    }

                    $('li', $searchTips).removeClass('active');
                    $($('li', $searchTips)[offset]).addClass('active');

                    this.value = autoComplete[offset];
                }
            } else if ((key >= 48 && key <= 90) || (key >= 188 && key <= 222) || key === 8 || key === 46) {
                //only go ahead if the keyCode is a "printable character" or erase / delete

                if (lastAutoCompleteXhr !== undefined) {
                    lastAutoCompleteXhr.abort();
                }

                requestData = {
                    'q' : q
                };

                persistUsernameValue = $('[name=persist-username]:checked').val();

                if (persistUsernameValue) {
                    requestData.u = persistUsernameValue;
                }

                lastAutoCompleteXhr = $.ajax({
                    data: requestData,
                    url: AppParams.searchEngineCdn + '?suggestion=true',
                    cache: true,
                    type: 'GET',
                    success: function (result) {
                        var counter,
                            lowerCasedQ = q.toLowerCase(),
                            autoCompleteHasQ,
                            html = '';

                        autoComplete = result;

                        autoCompleteHasQ = underscore.indexOf(autoComplete, lowerCasedQ);

                        //add the searched value to the autoComplete list, if already not there
                        if (autoComplete.length > 0 && autoCompleteHasQ !== 0) {
                            autoComplete.push(lowerCasedQ);
                        }

                        autoCompletePos = 0;
                        autoCompleteLength = autoComplete.length;

                        for (counter = 0; counter < autoCompleteLength; counter += 1) {
                            if (autoCompleteHasQ !== 0 && counter === autoCompleteLength - 1) {
                                html += '<li class="hidden">' + underscore.escape(autoComplete[counter]) + '</li>';
                            } else {
                                html += '<li>' + underscore.escape(autoComplete[counter]) + '</li>';
                            }
                        }

                        if (autoComplete.length > 0) {
                            $searchTips.removeClass('hidden').html(html);
                        } else {
                            hideSearchTips();
                        }
                    }
                });
            }
        });

        $(document.documentElement).on('keyup', function (e) {
            if (e.target.nodeName.toLowerCase() !== 'body') {
                return;
            }

            if (e.keyCode === 191) {
                // focus on search by pressing slash (/)
                $searchText.focus();
            }
        });
    });
