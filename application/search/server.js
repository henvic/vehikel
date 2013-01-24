#!/usr/bin/env node

/*jslint node: true */
/*global require */

module.exports = function () {
    "use strict";

    var settings = require("./settings");

    console.info("Loading search server\nListening on port %d (%s)", settings.port, settings.env);

    var util = require("util");

    var events = require("events");

    var http = require("http");

    var querystring = require("querystring");

    var url = require("url");

    var elastic = require("./library/elastic")(util, events, http);

    var requests = require("./library/requests")(util, events, http, querystring, url, elastic);

    var server = http.createServer();

    server.listen(settings.port);

    server.on("request", requests.load);

    server.addListener("request", requests.load);
};

module.exports();
