/*jslint node: true */
/*global require */

module.exports = function (Gearman, elasticManager, settings) {
    "use strict";

    var gearmanSettings = settings.gearman;

    var gearman = new Gearman(gearmanSettings.hostname, gearmanSettings.port);

    console.info(
        "Listening to gearman on %s:%d (%s)",
        settings.gearman.hostname, settings.gearman.port, settings.env
    );

    gearman.registerWorker("searchDeletePost", function (payload, worker) {
        var document;

        if (! payload) {
            console.log("Payload not found for the searchDeletePost job");
            worker.error();
            return;
        }

        var input = payload.toString("utf-8");

        try {
            document = JSON.parse(input);
        } catch (e) {
            console.error("Error parsing the expected JSON response (searchDeletePost job)");
            worker.error();
            return;
        }

        elasticManager.deleteDocument("post", document.type, document.id, function (isSuccess) {
            if (isSuccess) {
                worker.end();
            } else {
                worker.error();
            }
        });
    });

    gearman.registerWorker("searchIndexPost", function (payload, worker) {
        var document;

        if (! payload) {
            console.log("Payload not found for the searchIndexPost job");
            worker.error();
            return;
        }

        var input = payload.toString("utf-8");

        try {
            document = JSON.parse(input);
        } catch (e) {
            console.error("Error parsing the expected JSON response");
            worker.error();
            return;
        }

        elasticManager.storeDocument("posts", "post", document.id, input, function (isSuccess) {
            if (isSuccess) {
                worker.end();
            } else {
                worker.error();
            }
        });
    });
};
