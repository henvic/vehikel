/*global require, __utils__ */
/*jshint indent:4 */

var exports = function () {
    "use strict";

    var domAppParams;

    var getRandomPrice = function () {
        var min = 100;
        var max = 1000;

        return (Math.floor(Math.random() * (max - min + 1)) + min) * 100;
    };

    var getRandomYear = function () {
        var min = 1980;
        var max = 2014;

        return Math.floor(Math.random() * (max - min + 1)) + min;
    };

    var getRandomEngine = function () {
        var min = 10;
        var max = 68;

        var engine = (Math.floor(Math.random() * (max - min + 1)) + min).toString();

        return engine[0] + "." + engine[1];
    };

    var getVehiclesSample = function () {
        var vehiclesJson = fs.read("../../static/data/vehicles.json");
        var vehicles = JSON.parse(vehiclesJson);

        var vehiclesSample;

        vehicles.sort(function () {
            return 0.5 - Math.random();
        });

        vehiclesSample = vehicles.slice(0, 20);

        for (var item in vehiclesSample) {
            if(vehiclesSample.hasOwnProperty(item) ) {
                vehiclesSample[item].price = getRandomPrice();
                vehiclesSample[item].model_year = getRandomYear();
                vehiclesSample[item].engine = getRandomEngine();
            }
        }

        return vehiclesSample;
    };

    var addVehiclePicture = function (that, cookies, hash, action, filePath) {
        var page = require("webpage").create();

        page.content = '<html><body>' +
            '<form id="file-upload-casper" enctype="multipart/form-data" method="post">' +
            '<input type="file" name="Filedata" />' +
            '<input type="hidden" name="hash">' + '</form>' +
            '</body></html>';

        page.uploadFile("input[name=Filedata]", filePath);

        page.evaluate(function (action, hash) {
            document.querySelector("form").action = action;
            document.querySelector("input[name=hash]").value = hash;
            document.querySelector("form").submit();
        }, action, hash);

        page.onResourceReceived = function (response) {
            if (! page.plainText) {
                return;
            }

            try {
                var resourceResponse = JSON.parse(page.plainText);

                if (resourceResponse.picture_id) {
                    that.test.assertType(resourceResponse.picture_id, "string", "Add a random picture to a product");
                }
            } catch (e) {
                console.error("Failed to assert response");
            }

            page.close();
        };
    };

    var addVehiclePictures = function (domPageAppParams, type, that, casper) {
        var dirs = getPicturesDirByType(type);

        var randomDir = filesModel.getRandomDir(dirs);

        var picturesDir = execDirectoryPath + "/providers/pictures/" + type + "/" + randomDir;

        var randomFiles = filesModel.getRandomJpgFiles(picturesDir);

        var filesPaths = randomFiles.map(function (randomFile) {
            return picturesDir + "/" + randomFile;
        });

        var action = config.host + "/" + config.credentials.username + "/" + domPageAppParams.postId + "/picture/add";

        for (var i = 0, filesPathLength = filesPaths.length; i < filesPathLength; i++) {
            addVehiclePicture(that, casper.page.cookies, domPageAppParams.globalAuthHash, action, filesPaths[i]);
        }
    };

    var addVehicle = function (vehiclesSample, vehiclesSampleLength, vehiclePos, casper) {
        var data = vehiclesSample[vehiclePos];

        data.hash = domAppParams.globalAuthHash;

        var options = {
            method: "post",
            data: data
        };

        var domPageAppParams;

        casper.thenOpen(config.host + "/new", options, function (response) {
            domPageAppParams = this.getGlobal("AppParams");

            var postId = domPageAppParams.postId;

            this.test.assertTruthy(postId, "Post created, ID: " + domPageAppParams.postId);

            casper.thenEvaluate(function () {
                var setEquipment = function () {
                    var postEquipments = document.querySelectorAll('.post-equipments-list [name="equipment[]"]');

                    var equipmentListLength = postEquipments.length;

                    for (var counter = 0; counter < equipmentListLength; counter++) {
                        if (Math.random() > 0.35) {
                            postEquipments[counter].click();
                        }
                    }
                };

                var setOthers = function () {
                    var armorElement = document.querySelector("#post-product-info-others [name=armor]");

                    if (armorElement && Math.random() < 0.1) {
                        armorElement.click();
                    }

                    var handicappedElement = document.querySelector("#post-product-info-others [name=handicapped]");

                    if (handicappedElement && Math.random() < 0.1) {
                        handicappedElement.click();
                    }

                    var kmSelector = document.querySelector("#post-product-info-editing-area [name=km]");

                    if (kmSelector && Math.random() > 0.2) {
                        kmSelector.value = (Math.floor(Math.random() * (200 - 40 + 1)) + 40) * 450;
                        document.querySelector("#post-product-info-editing-area [type=submit]").click();
                    }
                };

                var publish = function () {
                    if (Math.random() > 0.4) {
                        document.querySelectorAll(".publish")[0].click();
                    }
                };

                setEquipment();

                setTimeout(setOthers, 1000);

                setTimeout(publish, 2000);
            });

            //give 7 seconds to allow everything to finish
            casper.then(function () {
                this.wait(7000, function() {
                });
            });

            casper.then(function () {
                if (vehiclePos < vehiclesSampleLength - 1) {
                    vehiclePos++;

                    addVehicle(vehiclesSample, vehiclesSampleLength, vehiclePos, casper);
                }
            });

            addVehiclePictures(domPageAppParams, data.type, this, casper);
        });
    };

    var authenticate = function (casper, callbackfn) {
        casper.thenOpen(config.host, function () {
            this.fill('form[action="/login"]', config.credentials, true);
        });

        casper.then(function () {
            this.test.assertHttpStatus(200, "Authentication complete with username / password");
            domAppParams = this.getGlobal("AppParams");

            callbackfn(casper);
        });
    };

    var addVehiclesSample = function (casper) {
        var vehiclesSample = getVehiclesSample();

        addVehicle(vehiclesSample, vehiclesSample.length, 0, casper);
    };

    var setUp = function () {
        var casper = require('casper').create({
            verbose: true
        });

        casper.start();

        authenticate(casper, addVehiclesSample);

        casper.run(function () {
            casper.test.done();
            this.echo("End of execution.");
            this.exit();
        });
    };

    var getExecDirectoryPath = function () {
        /**
         * Hack to retrieve the current directory,
         * because of a path error with casperjs
         * if you called casperjs test foo.js
         * it would set the path to the file ./test instead
         * of to ./foo.js as it should
         */
        var system = require("system");

        var path = fs.absolute(system.args[3]);

        path = path.split("/");

        path = path.splice(0, path.length - 1).join("/");

        return path;
    };

    var getPicturesDirByType = function (type) {
        if (typeof type === "string" && dirs[type] === undefined) {
            dirs[type] = filesModel.getDirs(execDirectoryPath + "/providers/pictures/" + type);
        }

        return dirs[type];
    };

    var loadConfig = function () {
        if (fs.exists(execDirectoryPath + "/settings.json.dist")) {
            return JSON.parse(fs.read(execDirectoryPath + "/settings.json.dist"));
        }

        return require(execDirectoryPath + "/settings.json");
    };

    var fs = require("fs");

    var execDirectoryPath = getExecDirectoryPath();

    var filesModel = require(execDirectoryPath + "/lib/files.js");

    var dirs = {};

    var config = loadConfig();

    setUp();
} ();
