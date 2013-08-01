/*jslint node: true */
/*global require */

module.exports = function (Gearman) {
    "use strict";

    exports.setUpGearman = function (hostname, port) {

        var gearman = new Gearman(hostname, port);

        console.info("Listening to gearman on %s:%d", hostname, port);

        gearman.on("connect", function () {
            console.info("Connected to Gearman!\n");
        });

        gearman.on("idle", function (s) {
            console.info("No jobs, resting!\n");
        });

        gearman.on("close", function () {
            console.error("Gearman connection closed!\n");
        });

        gearman.on("error", function () {
            console.error("Gearman error, disconnected!\n");
        });

        return gearman;
    };

    return exports;
};
