/*jslint node: true */
/*global require */

module.exports = function (util, events, http, querystring, url, elastic) {
    "use strict";

    var exports = {};

    var searchRequest = function (request, response) {
        request.setEncoding("utf8");
        response.setEncoding("utf8");

        var body = "";

        request.on("data", function (chunk) {
            body += chunk;
        });
        request.on("end", function () {
            if (request.url.length > 400) {
                response.writeHead(403, {"Content-Type": "text/plain"});
                response.end("Search string is too long.");
                return;
            }

            var requestUrl = url.parse(request.url.toLowerCase(), true);

            var search = new elastic.search();

            search.query(requestUrl.query);

            search.on("success", function (searchResponse) {
                var result;
                var resultJson;

                response.writeHead(200, {"Content-Type": "application/json"});

                if (requestUrl.query.suggestion !== undefined) {
                    if (searchResponse.facets !== undefined &&
                        searchResponse.facets.title_suggestions !== undefined &&
                        searchResponse.facets.title_suggestions.terms !== undefined
                        ) {
                        var terms = searchResponse.facets.title_suggestions.terms;

                        var suggestions = [];

                        for (var suggestionPos in terms) {
                            if (terms.hasOwnProperty(suggestionPos) && terms[suggestionPos].term !== undefined) {
                                suggestions.push(terms[suggestionPos].term);
                            }
                        }

                        result = suggestions;
                    } else {
                        response.writeHead(404, {"Content-Type": "text/plain"});
                        response.end("Missing data for the suggestions response.");
                    }
                } else {
                    result = searchResponse;
                }

                resultJson = JSON.stringify(result);

                response.end(resultJson);
            });

            search.on("failure", function (error) {
                response.writeHead(404, {"Content-Type": "text/plain"});
                response.end("Search failure.");
            });
        });
    };

    var notFoundRequest = function (request, response) {
        response.writeHead(404, {"Content-Type": "text/plain"});
        response.end("Not found.");
    };

    exports.load = function (request, response) {
        var requestUrl = url.parse(request.url);

        if (requestUrl.pathname === "/") {
            return searchRequest(request, response);
        } else {
            return notFoundRequest(request, response);
        }
    };

    return exports;
};
