/*jslint node: true */

module.exports = function (url, elastic) {
    "use strict";

    var exports = {},
        filterPublicPosts,
        searchRequest,
        notFoundRequest;

    /**
     * Filter the posts to only query the public posts available
     * @param query
     * @returns modified query
     */
    filterPublicPosts = function (query) {
        query.status = "active";

        if (query.user === undefined) {
            query.user = {};
        }

        query.user.active = "1";

        return query;
    };

    searchRequest = function (request, response) {
        request.setEncoding("utf8");

        var body = "";

        //hack to avoid jslint from saying body is not being used
        if (body) {
            console.log();
        }

        request.on("data", function (chuck) {
            body += chuck;
        });

        request.on("end", function () {
            var requestUrl,
                search;

            if (request.url.length > 400) {
                response.writeHead(403, {"Content-Type": "text/plain"});
                response.end("Search string is too long.");
                return;
            }

            requestUrl = url.parse(request.url.toLowerCase(), true);

            search = new elastic.search();

            search.query(filterPublicPosts(requestUrl.query));

            search.on("success", function (searchResponse) {
                var result,
                    resultJson,
                    terms,
                    suggestions = [],
                    suggestionPos;

                response.writeHead(200, {"Content-Type": "application/json"});

                if (requestUrl.query.suggestion !== undefined) {
                    if (searchResponse.facets !== undefined &&
                            searchResponse.facets.title_suggestions !== undefined &&
                            searchResponse.facets.title_suggestions.terms !== undefined) {
                        terms = searchResponse.facets.title_suggestions.terms;

                        for (suggestionPos in terms) {
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

            search.on("failure", function () {
                response.writeHead(404, {"Content-Type": "text/plain"});
                response.end("Search failure.");
            });
        });
    };

    /*jslint unparam: true */
    notFoundRequest = function (request, response) {
        response.writeHead(404, {"Content-Type": "text/plain"});
        response.end("Not found.");
    };
    /*jslint unparam: false */

    exports.load = function (request, response) {
        var requestUrl = url.parse(request.url);

        if (requestUrl.pathname === "/") {
            return searchRequest(request, response);
        }

        return notFoundRequest(request, response);
    };

    return exports;
};
