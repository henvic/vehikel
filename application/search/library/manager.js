/*jslint node: true */
/*global require */

module.exports = function (childProcess, gearman) {
    "use strict";

    var syncUserProfileOnPostsByUserId = function (id, syncedCallback, worker) {
        var filteredId = parseInt(id, 10);

        if (isNaN(filteredId)) {
            syncedCallback({error: true, message: "User ID received is not a number."});
            return;
        }

        var cmdParams = "--controller search --action sync-user-profile-on-posts --uid " + filteredId;

        var cmd = "php " + __dirname + "/../../../bin/services " + cmdParams;

        childProcess.exec(cmd, function (error, stdout, stderr) {
            if (error === null) {
                syncedCallback({error: false, message: stdout}, worker);
                return;
            }

            syncedCallback({error: true, message: stderr}, worker);
        });
    };

    var syncedCallback = function (result, worker) {
        if (result.error) {
            worker.error();
            console.error(result.message + "\n");
            return;
        }

        worker.end();
        console.info(result.message + "\n");
    };

    exports.setUpSyncUserProfileOnPostsFromUserWorker = function () {
        gearman.registerWorker("syncUserProfileOnPosts", function (payload, worker) {
            var uid = payload.toString("utf-8");

            syncUserProfileOnPostsByUserId(uid, syncedCallback, worker);

            worker.end();
        });
    };

    return exports;
};
