/*global define */
/*jslint browser: true */

define(['jquery', 'underscore', 'text!../../data/vehicles.json'], function ($, underscore, vehiclesJsonDataText) {
    'use strict';

    var exports = {},
        vehicles,
        getVehicles;

    getVehicles = function () {
        if (vehicles === undefined) {
            try {
                vehicles = JSON.parse(vehiclesJsonDataText);
            } catch (e) {
                vehicles = [];
            }
        }

        return vehicles;
    };

    exports.setUp = function ($typeSelector, $makeSelector, $modelSelector) {
        $typeSelector.on('change', function () {
            $makeSelector.val('').attr('disabled', 'disabled');
            $modelSelector.val('').attr('disabled', 'disabled');
            exports.loadPostProductMakes($makeSelector, $typeSelector.val());
        });

        exports.loadPostProductMakes($makeSelector, $typeSelector.val(), $makeSelector.val());
        exports.loadPostProductModels(
            $modelSelector,
            $typeSelector.val(),
            $makeSelector.val(),
            $modelSelector.val()
        );

        $makeSelector.on('change', function () {
            $modelSelector.val('').attr('disabled', 'disabled');

            exports.loadPostProductModels($modelSelector, $typeSelector.val(), $makeSelector.val());
        });
    };

    exports.loadPostProductMakes = function ($makeSelector, type, activeMake) {
        var filter,
            makes,
            $optGroup = $($makeSelector.find('optgroup')[0]),
            $entrySet = $('<select>');


        filter = {
            type: type
        };

        makes = underscore.uniq(underscore.pluck(underscore.where(getVehicles(), filter), 'make'));

        $entrySet.append('<option value="">-</option>');
        $.each(makes, function () {
            $entrySet.append($('<option>', { value: this }).text(this));
        });

        $entrySet.append($('<option>', { 'data-action' : 'other' }).text('Outro'));

        $makeSelector.removeAttr('disabled');
        $optGroup.html($entrySet.children());

        if (activeMake !== undefined) {
            $makeSelector.val(activeMake);

            if ($makeSelector.val() === activeMake) {
                return;
            }

            $optGroup.append($('<option>', { value: activeMake }).text(activeMake));
            $makeSelector.val(activeMake);
        }
    };

    exports.loadPostProductModels = function ($modelSelector, type, make, activeModel) {
        var filter,
            models,
            $optGroup,
            $entrySet;

        filter = {
            type: type,
            make: make
        };

        models = underscore.uniq(
            underscore.pluck(
                underscore.where(getVehicles(), filter),
                'model'
            )
        );

        $modelSelector.html('<optgroup label="Modelo"><option value="">-</option></optgroup>');
        $optGroup = $($modelSelector.find('optgroup')[0]);
        $entrySet = $('<select>');
        $.each(models, function () {
            $entrySet.append($('<option>', { value: this }).text(this));
        });

        $entrySet.append($('<option>', { 'data-action' : 'other' }).text('Outro'));

        $modelSelector.removeAttr('disabled');
        $optGroup.append($entrySet.children());

        if (activeModel !== undefined) {
            $modelSelector.val(activeModel);

            if ($modelSelector.val() === activeModel) {
                return;
            }

            $optGroup.append($('<option>', { value: activeModel }).text(activeModel));
            $modelSelector.val(activeModel);
        }
    };

    exports.maskMoney = function ($element) {
        $element.maskMoney(
            {
                symbol: 'R$ ',
                showSymbol: true,
                symbolStay: true,
                thousands: '.',
                decimal: ',',
                defaultZero: false
            }
        );
    };

    exports.parseYouTubeIdFromLink = function (link, softPass) {
        //from http://stackoverflow.com/posts/10591582/revisions

        /*jslint regexp: true*/
        var id = link.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/);
        /*jslint regexp: false*/

        if (id !== null) {
            return id[1];
        }

        if (softPass) {
            return link;
        }

        return '';
    };

    return exports;
});
