/*global require, __utils__ */
/*jshint indent:4 */

var require = patchRequire(require);
var utils = require('utils');

var exports = function () {
    "use strict";

    var fs = require("fs");

    exports.getDirs = function (path) {
        var list = fs.list(path);

        var listLength = list.length;

        if (! listLength) {
            console.error("Directory " + path + " was not found.");
            return [];
        }

        var dirs = [];

        for (var counter = 0; counter < listLength; counter++) {
            var dirName = list[counter];
            var dirPath = path + "/" + dirName;
            if (dirName !== "." && dirName !== ".." && fs.isDirectory(dirPath)) {
                dirs.push(dirName);
            }
        }

        return dirs;
    };

    exports.getRandomDir = function (dirs) {
        var min = 0;
        var max = dirs.length - 1;

        var pos = Math.floor(Math.random() * (max - min + 1)) + min;

        return dirs[pos];
    };

    exports.getRandomJpgFiles = function (path) {
        var list = fs.list(path);

        var listLength = list.length;

        if (! listLength) {
            console.error("Directory " + path + " was not found.");
            return [];
        }

        var files = [];

        for (var counter = 0; counter < listLength; counter++) {
            var file = list[counter];
            var filePath = path + "/" + file;
            if (fs.isFile(filePath) && file.slice(file.length - 4) === ".jpg") {
                files.push(file);
            }
        }

        files.sort(function() {
            return 0.5 - Math.random();
        });

        //get up to 10 files
        var limit = Math.floor(Math.random() * (8)) + 3;

        return files.slice(0, limit);
    };
} ();
