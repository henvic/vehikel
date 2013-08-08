/*global define, window */
/*jshint indent:4 */

define([], function () {
    "use strict";

    var exports = {};

    /**
     * Convert number of bytes into human readable format
     * from http://codeaid.net/javascript/convert-size-in-bytes-to-human-readable-format-(javascript)
     *
     * @param bytes Number of bytes to convert
     * @param precision Number of digits after the decimal separator
     * @return string
     */
    exports.convertBytesToSize = function (bytes, precision) {
        var kilobyte = 1024;
        var megabyte = kilobyte * 1024;
        var gigabyte = megabyte * 1024;
        var terabyte = gigabyte * 1024;

        if ((bytes >= 0) && (bytes < kilobyte)) {
            return bytes + ' B';
        }

        if ((bytes >= kilobyte) && (bytes < megabyte)) {
            return (bytes / kilobyte).toFixed(precision) + ' KB';
        }

        if ((bytes >= megabyte) && (bytes < gigabyte)) {
            return (bytes / megabyte).toFixed(precision) + ' MB';
        }

        if ((bytes >= gigabyte) && (bytes < terabyte)) {
            return (bytes / gigabyte).toFixed(precision) + ' GB';
        }

        if (bytes >= terabyte) {
            return (bytes / terabyte).toFixed(precision) + ' TB';
        }

        return bytes + ' B';
    };

    return exports;
});
