/*jslint node: true, nomen: true */

module.exports = function (childProcess, gearman) {
    'use strict';

    var syncUserProfileOnPostsByUserId,
        syncedCallback;

    syncUserProfileOnPostsByUserId = function (id, syncedCallback, worker) {
        var filteredId = parseInt(id, 10),
            cmdParams,
            cmd;

        if (isNaN(filteredId)) {
            syncedCallback({error: true, message: 'User ID received is not a number.'});
            return;
        }

        cmdParams = '--controller search --action sync-user-profile-on-posts --uid ' + filteredId;

        cmd = 'php ' + __dirname + '/../../../bin/services ' + cmdParams;

        childProcess.exec(cmd, function (error, stdout, stderr) {
            if (error === null) {
                syncedCallback({error: false, message: stdout}, worker);
                return;
            }

            syncedCallback({error: true, message: stderr}, worker);
        });
    };

    syncedCallback = function (result, worker) {
        if (result.error) {
            worker.error();
            console.error(result.message + '\n');
            return;
        }

        worker.end();
        console.info(result.message + '\n');
    };

    exports.setUpSyncUserProfileOnPostsFromUserWorker = function () {
        gearman.registerWorker('syncUserProfileOnPosts', function (payload, worker) {
            var uid = payload.toString('utf-8');

            syncUserProfileOnPostsByUserId(uid, syncedCallback, worker);

            worker.end();
        });
    };

    return exports;
};
