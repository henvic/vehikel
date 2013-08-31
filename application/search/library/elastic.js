/*jslint node: true */

module.exports = function (util, events, http, settings) {
    'use strict';

    var setFilters,
        getFilterString,
        getFilterTypes,
        search;

    setFilters = function (query, filter, filterList) {
        var filterLength = filterList.length,
            field,
            fieldName,
            fieldValue,
            thisFilter,
            pos;

        for (pos = 0; pos < filterLength; pos = pos + 1) {
            field = filterList[pos].field;
            fieldName = filterList[pos].name;

            if (query[fieldName] !== undefined) {
                fieldValue = getFilterString(query[fieldName]);

                if (fieldValue !== undefined) {
                    thisFilter = {};
                    thisFilter.term = {};
                    thisFilter.term[field] = fieldValue;
                    filter.and.push(thisFilter);
                }
            }
        }
    };

    getFilterString = function (input) {
        var value;

        if (typeof input === 'string') {
            value = input.toLowerCase();
        }

        return value;
    };

    getFilterTypes = function (input) {
        var type = [],
            isArrayOfStrings;

        if (input !== undefined) {
            if (typeof input === 'string') {
                type.push(input);
            } else if (Array.isArray(input)) {
                isArrayOfStrings = input.every(function (element) {
                    return (typeof element === 'string');
                });

                if (isArrayOfStrings) {
                    type = input;
                }
            }
        }

        return type;
    };

    search = function () {
        events.EventEmitter.call(this);
    };

    util.inherits(search, events.EventEmitter);

    search.prototype.query = function (query) {
        var that = this,
            filter,
            highlight,
            requestData,
            q,
            qSuggestion,
            priceMin,
            priceMax,
            filterPrice,
            filterTypesList,
            filterTypes,
            priceSort,
            qSuggestionEscaped,
            sendBuffer,
            from,
            size,
            postRequest,
            buffer,
            req;

        filter = {
            'and': [
            ]
        };

        highlight = {
            'fields': {
                'title': {}
            },
            'encoder': 'html'
        };

        requestData = {
            'query': {
                'filtered': {
                    query: {}
                }
            },
            'highlight': highlight
        };

        q = '';
        qSuggestion = '';

        if (query.q !== undefined && typeof query.q === 'string' && query.q !== '') {
            if (query.suggestion !== undefined) {
                q = '*';
                qSuggestion = query.q;
            } else {
                q = query.q;
                qSuggestion = q;
            }
        } else {
            q = '*';
            qSuggestion = '*';
        }

        requestData.query.filtered.query.query_string = {
            'query': q,
            'default_operator': 'AND'
        };

        if (query['price-min'] !== undefined && typeof query['price-min'] === 'string') {
            priceMin = query['price-min'].match(/[\d]/g).join('');
            priceMin = parseInt(priceMin, 10);
        }

        if (query['price-max'] !== undefined && typeof query['price-max'] === 'string') {
            priceMax = parseInt(query['price-max'].match(/[\d]/g).join(''), 10);
            priceMax = parseInt(priceMax, 10);
        }

        if (!isNaN(priceMin) || !isNaN(priceMax)) {
            filterPrice = {};
            filterPrice.numeric_range = {};
            filterPrice.numeric_range.price = {};
            if (!isNaN(priceMin)) {
                filterPrice.numeric_range.price.from = priceMin;
            }

            if (!isNaN(priceMax)) {
                filterPrice.numeric_range.price.to = priceMax;
            }

            filter.and.push(filterPrice);
        }

        filterTypesList = getFilterTypes(query.type);

        if (filterTypesList.length !== 0) {
            filterTypes = {
                'terms': {
                    'type': filterTypesList
                }
            };

            filter.and.push(filterTypes);
        }

        setFilters(
            query,
            filter,
            [
                {
                    name: 'make',
                    field: 'make.lowercase'
                },
                {
                    name: 'model',
                    field: 'model.lowercase'
                },
                {
                    name: 'year',
                    field: 'year'
                },
                {
                    name: 'status',
                    field: 'status'
                },
                {
                    name: 'where',
                    field: 'user.where.lowercase'
                },
                {
                    name: 'u',
                    field: 'user.username.lowercase'
                },
                {
                    name: 'user.active',
                    field: 'user.active'
                },
                {
                    name: 'transmission',
                    field: 'transmission'
                },
                {
                    name: 'traction',
                    field: 'traction'
                },
                {
                    name: 'armor',
                    field: 'armor'
                },
                {
                    name: 'handicapped',
                    field: 'handicapped'
                },
                {
                    name: 'collection',
                    field: 'collection'
                }
            ]
        );

        if (filter.and.length !== 0) {
            requestData.query.filtered.filter = filter;
        }

        if (query.sort !== undefined) {
            if (query.sort === 'price-min' || query.sort === 'price-max') {
                if (query.sort === 'price-min') {
                    priceSort = 'asc';
                } else {
                    priceSort = 'desc';
                }

                requestData.sort = [
                    {
                        'price': {
                            'order': priceSort,
                            'missing': '_last',
                            'ignore_unmapped': true
                        }
                    }
                ];
            } else {
                requestData.sort = [
                    {
                        'id': {
                            'order': 'desc',
                            'missing': '_last',
                            'ignore_unmapped': true
                        }
                    }
                ];
            }
        }

        if (query.suggestion !== undefined) {
            // see http://stackoverflow.com/questions/3446170/escape-string-for-use-in-javascript-regex
            qSuggestionEscaped = qSuggestion.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
            requestData.facets = {
                'title_suggestions': {
                    'terms': {
                        'field': 'title.suggestions',
                        'regex': '^' + qSuggestionEscaped.toLowerCase() + '.*',
                        'size': 10,
                        'order': 'term'
                    }
                }
            };
        } else {
            requestData.facets = {
                'type': {
                    'terms': {
                        'field': 'type'
                    }
                },
                'make': {
                    'terms': {
                        'field': 'make.lowercase',
                        'size': 30
                    }
                },
                'model': {
                    'terms': {
                        'field': 'model.lowercase',
                        'size': 30
                    }
                },
                'year': {
                    'terms': {
                        'field': 'year',
                        'size': 30
                    }
                },
                'where': {
                    'terms': {
                        'field': 'user.where.lowercase',
                        'size': 30
                    }
                },
                'u': {
                    'terms': {
                        'field': 'user.username.lowercase',
                        'size': 30
                    }
                },
                'transmission': {
                    'terms': {
                        'field': 'transmission'
                    }
                },
                'traction': {
                    'terms': {
                        'field': 'traction'
                    }
                },
                'armor': {
                    'terms': {
                        'field': 'armor'
                    }
                },
                'handicapped': {
                    'terms': {
                        'field': 'handicapped'
                    }
                },
                'collection': {
                    'terms': {
                        'field': 'collection'
                    }
                }
            };

            requestData.fields = [
                'id',
                'universal_id',
                'title',
                'price',
                'year',
                'transmission',
                'fuel',
                'km',
                'armor',
                'handicapped',
                'collection',
                'pictures',
                'traction',
                'user.username',
                'user.where'
            ];
        }

        sendBuffer = JSON.stringify(requestData);
        from = 0;
        size = 10;

        if (query.facets !== undefined || query.suggestion !== undefined) {
            size = 0;
        } else if (query.size !== undefined && !isNaN(query.size)) {
            size = parseInt(query.size, 10);
        }

        if (query.from !== undefined && !isNaN(query.from)) {
            from = parseInt(query.from, 10) - 1;
        }

        postRequest = {
            hostname: settings.elastic.hostname,
            path: '/posts/post/_search?size=' + encodeURIComponent(size) + '&from=' + encodeURIComponent(from),
            port: settings.elastic.port,
            method: 'GET',
            headers: {
                'Content-Length': Buffer.byteLength(sendBuffer)
            }
        };

        buffer = '';

        req = http.request(postRequest, function (res) {
            res.setEncoding('utf8');
            res.on('data', function (data) {
                buffer += data;
            });
            res.on('end', function () {
                if (res.statusCode === 200) {
                    var searchResponse;

                    try {
                        searchResponse = JSON.parse(buffer);
                    } catch (e) {
                        console.error('Error parsing the expected JSON response');

                        that.emit('failure', {
                            errorCode: 'parsing-response'
                        });

                        return;
                    }

                    that.emit('success', searchResponse);
                } else {
                    console.warn('Request status code is %s', res.statusCode);
                    that.emit('failure', {
                        errorCode: res.statusCode
                    });
                }
            });
        });
        req.on('error', function () {
            that.emit('failure', {
                error: true,
                errorCode: 'requestError'
            });
            console.log('elasticsearch request error');
        });
        req.write(sendBuffer);
        req.end();
    };

    exports.search = search;

    return exports;
};
