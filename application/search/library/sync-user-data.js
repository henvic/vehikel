/*jslint node: true */
/*global __dirname, require */

module.exports = function (util, exec) {
    "use strict";

    var exports = {};

    var syncByUserId = function (id, successCallback) {
        var filteredId = parseInt(id, 10);

        if (isNaN(filteredId)) {
            console.error("User ID received by syncByUserId is not a number\n");
            successCallback(true);
            return;
        }

        var cmd = "php " + __dirname +
            "/../../../bin/services --controller search --action sync --uid " +
            filteredId;

        var child;

        child = exec(cmd, function (error, stdout, stderr) {
            console.info(stdout + "\n");
            if (error !== null) {
                successCallback(false);
                util.print('stderr: ' + stderr + "\n");
            } else {
                successCallback(true);
            }
        });
    };

    exports.byUserId = syncByUserId;

    return exports;
};
