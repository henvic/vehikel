/*global define, window */
/*jshint indent:4 */

define(["AppParams", "jquery", "underscore"],
    function (AppParams, $, underscore) {
        "use strict";

        var $searchResults = $("#search-results");

        $searchResults.on("click", ".posts-table-view tr", function (e) {
            var link = e.currentTarget.getAttribute("data-link");

            if (e.target.tagName.toLowerCase() !== "a") {
                e.preventDefault();
                window.location = link;
            }
        });

        var $searchText = $("#search-text");
        var $searchTips = $("#search-tips");

        var hideSearchTips = function () {
            $searchTips.html("").addClass("hidden");
        };

        var loadSearch = function () {
            var q = $searchText.val();
            if (AppParams.route !== "search/engine") {
                window.location = AppParams.webroot + "/search?q=" + encodeURIComponent(q);
            } else {
                hideSearchTips();
            }
        };

        $searchTips.on("mouseenter", "li", function (e) {
            $("li", $searchTips).removeClass("active");
            $(this).addClass("active");
        });

        $searchTips.on("mouseleave", "li", function (e) {
            $("li", $searchTips).removeClass("active");
        });

        $searchTips.on("click", "li", function (e) {
            $searchText.val(this.innerHTML);
            loadSearch();
        });

        var autoComplete = [];
        var autoCompletePos = 0;
        var autoCompleteLength = 0;
        var lastAutoCompleteXhr;

        $searchText.on("blur", function (e) {
            setTimeout(hideSearchTips, 300);
        });

        // prevent the cursor from moving when the up and down arrows are used
        $searchText.on("keydown", function (e) {
            if (e.keyCode === 38 || e.keyCode === 40) {
                e.preventDefault();
            }
        });

        $searchText.on("keyup", function (e) {
            var key = e.keyCode;
            var q = this.value;

            if (q === "") {
                hideSearchTips();
            } else if (key === 13) {
                loadSearch();
            } else if (key === 27) {
                hideSearchTips();
            } else if (key === 38 || key === 40) { //if key is up or down
                var isUp = (key === 38);
                var offset;

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

                    $("li", $searchTips).removeClass("active");
                    $($("li", $searchTips)[offset]).addClass("active");

                    this.value = autoComplete[offset];
                }
            } else if ((key >= 48 && key <= 90) || (key >= 188 && key <= 222) || key === 8 || key === 46) {
                //only go ahead if the keyCode is a "printable character" or erase / delete

                if (lastAutoCompleteXhr !== undefined) {
                    lastAutoCompleteXhr.abort();
                }

                lastAutoCompleteXhr = $.ajax({
                    data: {
                        "q" : q
                    },
                    url: AppParams.webroot + "/search-engine?suggestion=true",
                    cache: true,
                    type: "GET",
                    success: function (result) {
                        autoComplete = result;

                        var lowerCasedQ = q.toLowerCase();

                        var autoCompleteHasQ = underscore.indexOf(autoComplete, lowerCasedQ);

                        //add the searched value to the autoComplete list, if already not there
                        if (autoComplete.length > 0 && autoCompleteHasQ !== 0) {
                            autoComplete.push(lowerCasedQ);
                        }

                        autoCompletePos = 0;
                        autoCompleteLength = autoComplete.length;

                        var html = "";

                        for (var counter = 0; counter < autoCompleteLength; counter++) {
                            if (autoCompleteHasQ !== 0 && counter === autoCompleteLength - 1) {
                                html += '<li class="hidden">' + underscore.escape(autoComplete[counter]) + "</li>";
                            } else {
                                html += "<li>" + underscore.escape(autoComplete[counter]) + "</li>";
                            }
                        }

                        $searchTips.removeClass("hidden").html(html);
                    }
                });
            }
        });

        $(document.documentElement).on("keyup", function (e) {
            if (e.target.nodeName.toLowerCase() !== "body") {
                return;
            }

            if (e.keyCode === 191) {
                // focus on search by pressing slash (/)
                $searchText.focus();
            }
        });

        return function () {};
    });
