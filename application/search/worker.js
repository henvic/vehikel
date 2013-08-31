#!/usr/bin/env node

/*jslint node: true */

module.exports = function () {
    "use strict";

    var settings = require("./settings"),
        childProcess = require("child_process"),
        nodeGearman = require("node-gearman"),
        gearmanLibrary = require("./library/gearman")(nodeGearman),
        gearman = gearmanLibrary.setUpGearman(settings.gearman.hostname, settings.gearman.port),
        manager = require("./library/manager")(childProcess, gearman);

    manager.setUpSyncUserProfileOnPostsFromUserWorker();
};

module.exports();
