#!/usr/bin/env node

/*jslint node: true */
/*global require */

module.exports = function () {
    "use strict";

    var settings = require("./settings");

    var childProcess = require("child_process");

    var nodeGearman = require("node-gearman");

    var gearmanLibrary = require("./library/gearman")(nodeGearman);
    var gearman = gearmanLibrary.setUpGearman(settings.gearman.hostname, settings.gearman.port);

    var manager = require("./library/manager")(childProcess, gearman);
    manager.setUpSyncUserProfileOnPostsFromUserWorker();
};

module.exports();
