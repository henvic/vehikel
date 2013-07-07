/*global define */
/*jshint indent:4 */

define(["AppParams", "jquery", "underscore", "jquery.maskMoney"],
    function (AppParams, $, underscore) {
        "use strict";

        var exports = {};

        exports.transmissionTranslation = {
            "manual" : "manual",
            "automatic" : "automático",
            "other" : "outra"
        };

        exports.tractionTranslation = {
            "front" : "frontal",
            "rear" : "traseira",
            "4x4" : "4x4"
        };

        exports.parseQueryString = function (queryString) {
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

        exports.pageLink = function (formSerialized, page, pageSort) {
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

        exports.maskMoney = function ($element) {
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

        exports.formatMoney = function (money) {
            var $foo = $('<input type="text">');
            exports.maskMoney($foo);
            $foo.val(money);
            $foo.trigger("mask");
            return $foo.val();
        };

        exports.termListHtmlElementsType = function (terms, formSerialized, currentQueryStringParams) {
            var jsonFormSerialized = exports.parseQueryString(formSerialized.replace(/\+/g, ' '));
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
                    var escapedUrlRemove = AppParams.webroot +
                        "/search?" + $.param(jsonFormSerialized).replace(/%2B/g, '+')
                    ;

                    content += '<span class="label label-inverse">' +
                        underscore.escape(types[terms[termPos].term]) +
                        " (" + underscore.escape(terms[termPos].count) + ")" +
                        ' <a href="' + escapedUrlRemove + '" data-name="type" ' +
                        'data-value=""><i class="icon-remove icon-white"></i><span class="hidden"> remover</span></a>' +
                        "</span>";
                } else {
                    var escapedUrl = AppParams.webroot + "/search?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                    content += '<a href="' + escapedUrl + '" data-name="type" ' +
                        'data-value="' + underscore.escape(terms[termPos].term) + '">' +
                        underscore.escape(types[terms[termPos].term]) +
                        "</a> (" + underscore.escape(terms[termPos].count) + ")";
                }

                content += "</li>";
            }

            return content;
        };

        exports.termListHtmlElements = function (
            termName,
            terms,
            formSerialized,
            currentQueryStringParams,
            translationObject
            ) {
            var jsonFormSerialized = exports.parseQueryString(formSerialized.replace(/\+/g, ' '));
            var content = "";

            for (var termPos = 0, termLength = terms.length; termLength > termPos; termPos = termPos + 1) {
                if (! terms[termPos].term) {
                    continue;
                }

                jsonFormSerialized[termName] = underscore.escape(terms[termPos].term);

                var escapedUrl = AppParams.webroot + "/search?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                content += "<li>";

                var value = terms[termPos].term;

                if (translationObject !== undefined && translationObject[value] !== undefined) {
                    value = translationObject[value];
                }

                if (underscore.isEqual(currentQueryStringParams, jsonFormSerialized)) {
                    delete jsonFormSerialized[termName];
                    var escapedUrlRemove = AppParams.webroot +
                        "/search?" + $.param(jsonFormSerialized).replace(/%2B/g, '+')
                    ;

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

        exports.termListHtmlElementsBool = function (
            termName,
            terms,
            formSerialized,
            currentQueryStringParams,
            value
            ) {
            var jsonFormSerialized = exports.parseQueryString(formSerialized.replace(/\+/g, ' '));
            var content = "";

            for (var termPos = 0, termLength = terms.length; termLength > termPos; termPos = termPos + 1) {
                jsonFormSerialized[termName] = "1";

                var escapedUrl = AppParams.webroot + "/search?" + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                if (terms[termPos].term !== "T") {
                    continue;
                }

                content += "<li>";

                if (underscore.isEqual(currentQueryStringParams, jsonFormSerialized)) {
                    delete jsonFormSerialized[termName];
                    var escapedUrlRemove = AppParams.webroot +
                        "/search?" + $.param(jsonFormSerialized).replace(/%2B/g, '+')
                        ;

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

        return exports;
    }
);
