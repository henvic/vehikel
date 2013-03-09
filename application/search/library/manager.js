/*jslint node: true */
/*global require */

module.exports = function (syncUserData, Gearman, elasticManager, settings) {
    "use strict";

    var gearmanSettings = settings.gearman;

    var gearman = new Gearman(gearmanSettings.hostname, gearmanSettings.port);

    console.info(
        "Listening to gearman on %s:%d (%s)",
        settings.gearman.hostname, settings.gearman.port, settings.env
    );

    gearman.registerWorker("searchDelete", function (payload, worker) {
        var data;

        if (! payload) {
            console.log("Payload not found for the searchDelete job");
            worker.error();
            return;
        }

        var input = payload.toString("utf-8");

        try {
            data = JSON.parse(input);

            if (! data.index || ! data.type || ! data.id) {
                console.error("Missing parameters from the JSON response");
                worker.error();
                return;
            }

            if (! (
                typeof data.index === "string" &&
                    typeof data.type === "string" &&
                    (typeof data.id === "string" || typeof data.id === "number")
                )) {
                console.error("Wrong parameter(s) type(s) from the JSON response");
                worker.error();
                return;
            }
        } catch (e) {
            console.error("Error parsing the expected JSON response (searchDelete job)");
            worker.error();
            return;
        }

        elasticManager.deleteDocument(data.index, data.type, data.id, function (isSuccess) {
            if (isSuccess) {
                worker.end();
            } else {
                worker.error();
            }
        });
    });

    gearman.registerWorker("searchIndex", function (payload, worker) {
        var data;

        if (! payload) {
            console.log("Payload not found for the searchIndex job");
            worker.error();
            return;
        }

        var input = payload.toString("utf-8");

        try {
            data = JSON.parse(input);

            if (! data.index || ! data.type || ! data.id || ! data.document) {
                console.error("Missing parameters from the JSON response");
                worker.error();
                return;
            }

            if (! (
                typeof data.index === "string" &&
                typeof data.type === "string" &&
                (typeof data.id === "string" || typeof data.id === "number")
                )) {
                console.error("Wrong parameter(s) type(s) from the JSON response");
                worker.error();
                return;
            }
        } catch (e) {
            console.error("Error parsing the expected JSON response (searchIndex job)");
            worker.error();
            return;
        }

        elasticManager.storeDocument(data.index, data.type, data.id, data.document, function (isSuccess) {
            if (isSuccess) {
                worker.end();
            } else {
                worker.error();
            }
        });
    });

    gearman.registerWorker("syncUserData", function (payload, worker) {
        var input = payload.toString("utf-8");
        syncUserData.byUserId(input, function (isSuccess) {
            console.log(isSuccess);
            if (isSuccess) {
                worker.end();
            } else {
                worker.error();
            }
        });
        worker.end();
    });
};
