#!/usr/bin/env node

/*jslint node: true */
/*global require */

module.exports = function () {
    "use strict";

    var settings = require("./settings");

    console.info("Loading search worker");

    var util = require("util");

    var events = require("events");

    var http = require("http");

    var querystring = require("querystring");

    var url = require("url");

    var Gearman = require("node-gearman");

    var elasticManager = require("./library/elastic-manager")(util, events, http, settings);

    var manager = require("./library/manager");

    manager(Gearman, elasticManager, settings);
};

module.exports();
