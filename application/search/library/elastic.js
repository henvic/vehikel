/*jslint node: true */
/*global require */

module.exports = function (util, events, http) {
    "use strict";

    var setFilters = function (query, filter, filterList) {
        var filterLength = filterList.length;

        for (var pos = 0; pos < filterLength; pos++) {
            var field = filterList[pos].field;
            var fieldName = filterList[pos].name;

            if (query[fieldName] !== undefined) {
                var fieldValue = getFilterString(query[fieldName]);

                if (fieldValue !== undefined) {
                    var thisFilter = {};
                    thisFilter.term = {};
                    thisFilter.term[field] = fieldValue;
                    filter.and.push(thisFilter);
                }
            }
        }
    };

    var getFilterString = function (input) {
        var value;

        if (typeof input === "string") {
            value = input.toLowerCase();
        }

        return value;
    };

    var getFilterTypes = function (input) {
        var type = [];

        if (input !== undefined) {
            if (typeof input === "string") {
                type.push(input);
            } else if (Array.isArray(input)) {
                var isArrayOfStrings = input.every(function (element) {
                    return (typeof element === "string");
                });

                if (isArrayOfStrings) {
                    type = input;
                }
            }
        }

        return type;
    };

    var search = function () {
        events.EventEmitter.call(this);
    };

    util.inherits(search, events.EventEmitter);

    search.prototype.query = function (query) {
        var that = this;

        var filter = {
            "and" : [
            ]
        };

        var highlight = {
            "fields" : {
                "title" : {}
            },
            "encoder" : "html"
        };

        var requestData = {
            "query" : {
                "filtered" : {
                    query : {}
                }
            },
            "highlight" : highlight
        };

        var q = "";
        var qSuggestion = "";

        if (query.q !== undefined && typeof query.q === "string" && query.q !== "") {
            if (query.suggestion !== undefined) {
                q = "*";
                qSuggestion = query.q;
            } else {
                q = query.q;
                qSuggestion = q;
            }
        } else {
            q = "*";
            qSuggestion = "*";
        }

        requestData.query.filtered.query.query_string = {
            "query" : q,
            "default_operator" : "AND"
        };

        var priceMin;
        var priceMax;

        if (query["price-min"] !== undefined && typeof query["price-min"] === "string") {
            priceMin = query["price-min"].replace(/[^\d]/g, "");
            priceMin = parseInt(priceMin, 10);
        }

        if (query["price-max"] !== undefined && typeof query["price-max"] === "string") {
            priceMax = parseInt(query["price-max"].replace(/[^\d]/g, ""), 10);
            priceMax = parseInt(priceMax, 10);
        }

        if (! isNaN(priceMin) || ! isNaN(priceMax)) {
            var filterPrice = {};
            filterPrice.numeric_range = {};
            filterPrice.numeric_range.price = {};
            if (! isNaN(priceMin)) {
                filterPrice.numeric_range.price.from = priceMin;
            }

            if (! isNaN(priceMax)) {
                filterPrice.numeric_range.price.to = priceMax;
            }

            filter.and.push(filterPrice);
        }

        var filterTypesList = getFilterTypes(query.type);

        if (filterTypesList.length !== 0) {
            var filterTypes = {
                "terms" : {
                    "type" : filterTypesList
                }
            };

            filter.and.push(filterTypes);
        }

        setFilters(
            query,
            filter,
            [
                {
                    name : "make",
                    field : "make.lowercase"
                },
                {
                    name : "model",
                    field : "model.lowercase"
                },
                {
                    name : "year",
                    field : "year"
                },
                {
                    name : "where",
                    field : "user.where.lowercase"
                },
                {
                    name : "u",
                    field : "user.username.lowercase"
                }
            ]
        );

        if (filter.and.length !== 0) {
            requestData.query.filtered.filter = filter;
        }

        if (query.sort !== undefined) {
            if (query.sort === "price-min" || query.sort === "price-max") {
                var priceSort;

                if (query.sort === "price-min") {
                    priceSort = "asc";
                } else {
                    priceSort = "desc";
                }

                requestData.sort = [
                    {
                        "price" : {
                            "order" : priceSort,
                            "missing" : "_last",
                            "ignore_unmapped" : true
                        }
                    }
                ];
            }
        }

        if (query.suggestion !== undefined) {
            // see http://stackoverflow.com/questions/3446170/escape-string-for-use-in-javascript-regex
            var qSuggestionEscaped = qSuggestion.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
            requestData.facets = {
                "title_suggestions":{
                    "terms" : {
                        "field" : "title.suggestions",
                        "regex" : "^" + qSuggestionEscaped.toLowerCase() + ".*",
                        "size" : 10
                    }
                }
            };
        } else {
            requestData.facets = {
                "type" : {
                    "terms" : {
                        "field" : "type"
                    }
                },
                "make": {
                    "terms" : {
                        "field" : "make.lowercase",
                        "size" : 10
                    }
                },
                "model" : {
                    "terms" : {
                        "field" : "model.lowercase",
                        "size" : 10
                    }
                },
                "price" : {
                    "range" : {
                        "price" : [
                            { "from" : 0, "to" : 999999 },
                            { "from" : 1000000, "to" : 1999999 },
                            { "from" : 2000000, "to" : 2999999 },
                            { "from" : 3000000, "to" : 3999999 },
                            { "from" : 4000000, "to" : 4999999 },
                            { "from" : 5000000, "to" : 7999999 },
                            { "from" : 8000000, "to" : 8999999 },
                            { "from" : 9000000, "to" : 9999999 },
                            { "from" : 10000000 }
                        ]
                    }
                },
                "year": {
                    "terms" : {
                        "field" : "year",
                            "size" : 10
                    }
                },
                "where" : {
                    "terms" : {
                        "field" : "user.where.lowercase",
                        "size" : 10
                    }
                },
                "u" : {
                    "terms" : {
                        "field" : "user.username.lowercase",
                        "size" : 10
                    }
                }
            };
        }

        var sendBuffer = JSON.stringify(requestData);

        var from = 0;
        var size = 10;

        if (query.facets !== undefined || query.suggestion !== undefined) {
            size = 0;
        } else if (query.size !== undefined && ! isNaN(query.size)) {
            size = parseInt(query.size, 10);
        }

        if (query.from !== undefined && ! isNaN(query.from)) {
            from = parseInt(query.from, 10) - 1;
        }

        var postRequest = {
            hostname: "localhost",
            path: "/posts/post/_search?size=" + encodeURIComponent(size) + "&from=" + encodeURIComponent(from),
            port: 9200,
            method: "GET",
            headers: {
                "Content-Length": sendBuffer.length
            }
        };

        var buffer = "";

        var req = http.request(postRequest, function (res) {
            res.on("data", function (data) {
                buffer += data;
            });
            res.on("end", function() {
                if (res.statusCode === 200) {
                    var searchResponse;

                    try {
                        searchResponse = JSON.parse(buffer);
                    } catch (e) {
                        console.error("Error parsing the expected JSON response");

                        that.emit("failure", {
                            errorCode: "parsing-response"
                        });

                        return;
                    }

                    that.emit("success", searchResponse);
                } else {
                    console.warn("Request status code is %s", res.statusCode);
                    that.emit("failure", {
                        errorCode: res.statusCode
                    });
                }
            });
        });
        req.on("error", function (error) {
            that.emit("failure", {
                error: true,
                errorCode: "requestError"
            });
            console.log("elasticsearch request error");
        });
        req.write(sendBuffer);
        req.end();
    };

    exports.search = search;

    return exports;
};
