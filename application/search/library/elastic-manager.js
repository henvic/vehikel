/*jslint node: true */
/*global require */

module.exports = function (util, events, http, settings) {
    "use strict";

    var elasticSettings = settings.elastic;

    exports.deleteDocument = function (index, type, id) {

        var postRequest = {
            hostname: elasticSettings.hostname,
            path: "/" + index + "/" + type + "/" + id,
            port: elasticSettings.port,
            method: "DELETE"
        };

        var req = http.request(postRequest, function (res) {
            res.on("end", function() {
                if (typeof res.statusCode === "number" && res.statusCode >= 200 && res.statusCode <= 299) {
                    console.info("Document " + postRequest.path + "deleted");
                } else {
                    console.info("Document " + postRequest.path + "not deleted");
                }
            });
        });
        req.on("error", function (error) {
            console.error("Error when trying to delete document " + postRequest.path);
        });
        req.end();
    };

    exports.storeDocument = function (index, type, id, data) {

        if (typeof data === "object") {
            data = JSON.stringify(data);
        }

        var postRequest = {
            hostname: elasticSettings.hostname,
            path: "/" + index + "/" + type + "/" + id,
            port: elasticSettings.port,
            method: "PUT",
            headers: {
                "Content-Length": data.length
            }
        };

        var buffer = "";

        var req = http.request(postRequest, function (res) {
            res.on("data", function (data) {
                buffer += data;
            });
            res.on("end", function() {
                if (typeof res.statusCode === "number" && res.statusCode >= 200 && res.statusCode <= 299) {
                    var response;

                    try {
                        response = JSON.parse(buffer);
                    } catch (e) {
                        console.error("Error in parsing the expected JSON response for document " + postRequest.path);
                        return;
                    }

                    console.info("Document " + postRequest.path + " stored");
                } else {
                    console.error("Failure in storing document " + postRequest.path);
                }
            });
        });
        req.on("error", function (error) {
            console.error("error when trying to store document " + postRequest.path);
        });
        req.write(data);
        req.end();
    };

    return exports;
};
