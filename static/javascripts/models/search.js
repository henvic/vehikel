/*global define */
/*jslint browser: true */

define(['AppParams', 'jquery', 'underscore', 'jquery.maskMoney'],
    function (AppParams, $, underscore) {
        'use strict';

        var exports = {},
            getTermItem;

        exports.transmissionTranslation = {
            'manual' : 'manual',
            'automatic' : 'automático',
            'other' : 'outra'
        };

        exports.tractionTranslation = {
            'front' : 'frontal',
            'rear' : 'traseira',
            '4x4' : '4x4'
        };

        exports.parseQueryString = function (queryString) {
            //from http://www.joezimjs.com/javascript/3-ways-to-parse-a-query-string-in-a-url/
            var params = {},
                queries,
                temp,
                i,
                l;

            queryString = queryString.replace(/\+/g, ' ');

            // Split into key/value pairs
            queries = queryString.split('&');

            if (queries.length === 1 && queries[0] === '') {
                queries = ['q='];
            }

            // Convert the array of strings into an object
            for (i = 0, l = queries.length; i < l; i = i + 1) {
                temp = queries[i].split('=');
                params[temp[0]] = temp[1];
            }

            if (params.q === undefined) {
                params.q = '';
            }

            delete params['persist-username'];
            delete params['search-go'];

            return params;
        };

        exports.parseLocationQueryString = function () {
            return exports.parseQueryString(window.location.search.substr(1));
        };

        exports.pageLink = function (formSerialized, page, pageSort) {
            if (formSerialized === '') {
                if (page === 1) {
                    if (pageSort) {
                        return '?sort=' + pageSort;
                    }
                    return '';
                }

                if (pageSort) {
                    return '?page=' + page + '&sort=' + pageSort;
                }

                return '?page=' + page;
            }

            if (page === 1) {
                if (pageSort) {
                    return '?' + formSerialized + '&sort=' + pageSort;
                }

                return '?' + formSerialized;
            }

            if (pageSort) {
                return '?' + formSerialized + '&page=' + page + '&sort=' + pageSort;
            }

            return '?' + formSerialized + '&page=' + page;
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
            $foo.trigger('mask');
            return $foo.val();
        };

        getTermItem = function (jsonFormSerialized, name, terms, pos, translationObj, currentQParams) {
            var content = '<li>',
                escapedUrl,
                value,
                escapedUrlRemove;

            jsonFormSerialized[name] = underscore.escape(terms[pos].term);

            escapedUrl = AppParams.webroot + '/search?' + $.param(jsonFormSerialized).replace(/%2B/g, '+');

            value = terms[pos].term;

            if (translationObj !== undefined && translationObj[value] !== undefined) {
                value = translationObj[value];
            }

            if (underscore.isEqual(currentQParams, jsonFormSerialized)) {
                delete jsonFormSerialized[name];
                escapedUrlRemove = AppParams.webroot +
                    '/search?' + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                content += '<span class="label label-inverse">' +
                    underscore.escape(value) +
                    ' (' + underscore.escape(terms[pos].count) + ')' +
                    ' <a href="' + escapedUrlRemove + '" data-name="' +
                    underscore.escape(name) + '" ' +
                    'data-value=""><i class="icon-remove icon-white"></i><span class="hidden"> remover</span></a>' +
                    '</span>';
            } else {
                content += '<a href="' + escapedUrl + '" data-name="' +
                    underscore.escape(name) + '" ' +
                    'data-value="' + underscore.escape(terms[pos].term) + '">' +
                    underscore.escape(value) +
                    '</a> (' + underscore.escape(terms[pos].count) + ')';
            }

            content += '</li>';

            return content;
        };

        exports.termListHtmlElementsType = function (terms, formSerialized, currentQParams) {
            var jsonFormSerialized,
                types,
                content = '',
                pos,
                termLength;

            jsonFormSerialized = exports.parseQueryString(formSerialized);

            types = {
                car: 'carro',
                motorcycle: 'motocicleta',
                boat: 'embarcação'
            };

            for (pos = 0, termLength = terms.length; termLength > pos; pos = pos + 1) {
                content += getTermItem(jsonFormSerialized, 'type', terms, pos, types, currentQParams);
            }

            return content;
        };

        exports.termListHtmlElements = function (
            name,
            terms,
            formSerialized,
            currentQParams,
            translationObj
        ) {
            var jsonFormSerialized,
                content = '',
                pos,
                termLength;

            jsonFormSerialized = exports.parseQueryString(formSerialized);

            for (pos = 0, termLength = terms.length; termLength > pos; pos = pos + 1) {
                if (terms[pos].term) {
                    content += getTermItem(jsonFormSerialized, name, terms, pos, translationObj, currentQParams);
                }
            }

            return content;
        };

        exports.termListHtmlElementsBool = function (
            name,
            terms,
            formSerialized,
            currentQParams,
            value
        ) {
            var jsonFormSerialized,
                content = '',
                pos,
                termLength,
                escapedUrl,
                escapedUrlRemove;

            jsonFormSerialized = exports.parseQueryString(formSerialized);

            for (pos = 0, termLength = terms.length; termLength > pos; pos = pos + 1) {
                jsonFormSerialized[name] = '1';

                escapedUrl = AppParams.webroot + '/search?' + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                if (terms[pos].term === 'T') {
                    content += '<li>';

                    if (underscore.isEqual(currentQParams, jsonFormSerialized)) {
                        delete jsonFormSerialized[name];
                        escapedUrlRemove = AppParams.webroot +
                            '/search?' + $.param(jsonFormSerialized).replace(/%2B/g, '+');

                        content += '<span class="label label-inverse">' +
                            underscore.escape(value) +
                            ' (' + underscore.escape(terms[pos].count) + ')' +
                            ' <a href="' + escapedUrlRemove + '" data-name="' +
                            underscore.escape(name) + '" data-value="">' +
                            '<i class="icon-remove icon-white"></i><span class="hidden"> remover</span></a>' +
                            '</span>';
                    } else {
                        content += '<a href="' + escapedUrl + '" data-name="' +
                            underscore.escape(name) + '" ' +
                            'data-value="1">' +
                            underscore.escape(value) +
                            '</a> (' + underscore.escape(terms[pos].count) + ')';
                    }

                    content += '</li>';
                }
            }

            return content;
        };

        return exports;
    });
