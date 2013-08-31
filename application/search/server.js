#!/usr/bin/env node

/*jslint node: true */

module.exports = function () {
    "use strict";

    var settings = require("./settings"),
        util = require("util"),
        events = require("events"),
        http = require("http"),
        url = require("url"),
        elastic = require("./library/elastic")(util, events, http, settings),
        requests = require("./library/requests")(url, elastic),
        server;

    console.info("Loading search server\nListening on port %d (%s)", settings.port, settings.env);

    server = http.createServer();

    server.listen(settings.port);

    server.on("request", requests.load);

    server.addListener("request", requests.load);
};

module.exports();
