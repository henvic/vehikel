/*global define, window */
/*jshint indent:4 */

define(["jquery", "underscore", "text!../../data/vehicles.json"], function ($, underscore, vehiclesJsonDataText) {
    "use strict";

    var exports = {};

    var vehicles;

    var getVehicles = function () {
        if (vehicles === undefined) {
            try {
                vehicles = JSON.parse(vehiclesJsonDataText);
            } catch (e) {
                vehicles = [];
            }
        }

        return vehicles;
    };

    exports.setUp = function ($typeSelector, $makeSelector, $modelSelector, make, model, type) {
        $typeSelector.on("change", function (e) {
            $makeSelector.val("").attr("disabled", "disabled");
            $modelSelector.val("").attr("disabled", "disabled");
            exports.loadPostProductMakes($makeSelector, $typeSelector.val());
        });

        exports.loadPostProductMakes($makeSelector, $typeSelector.val(), $makeSelector.val());
        exports.loadPostProductModels(
            $modelSelector,
            $typeSelector.val(),
            $makeSelector.val(),
            $modelSelector.val()
        );

        $makeSelector.on("change", function (e) {
            $modelSelector.val("").attr("disabled", "disabled");

            exports.loadPostProductModels($modelSelector, $typeSelector.val(), $makeSelector.val());
        });
    };

    exports.loadPostProductMakes = function ($makeSelector, type, activeMake) {
        var filter = {
            type : type
        };

        var makes = underscore.uniq(
            underscore.pluck(
                underscore.where(getVehicles(), filter), "make"
            )
        );

        var $optGroup = $($makeSelector.find("optgroup")[0]);
        var $entrySet = $("<select>");
        $entrySet.append('<option value="">-</option>');
        $.each(makes, function (key, value) {
            $entrySet.append($("<option>", { value : value }).text(value));
        });

        $entrySet.append($("<option>", { "data-action" : "other" }).text("Outro"));

        $makeSelector.removeAttr("disabled");
        $optGroup.html($entrySet.children());

        if (activeMake !== undefined) {
            $makeSelector.val(activeMake);

            if ($makeSelector.val() === activeMake) {
                return;
            }

            $optGroup.append($("<option>", { value : activeMake }).text(activeMake));
            $makeSelector.val(activeMake);
        }
    };

    exports.loadPostProductModels = function ($modelSelector, type, make, activeModel) {
        var filter = {
            type: type,
            make: make
        };

        var models = underscore.uniq(
            underscore.pluck(
                underscore.where(getVehicles(), filter),
                "model"
            )
        );

        $modelSelector.html('<optgroup label="Modelo"><option value="">-</option></optgroup>');
        var $optGroup = $($modelSelector.find("optgroup")[0]);
        var $entrySet = $("<select>");
        $.each(models, function (key, value) {
            $entrySet.append($("<option>", { value : value }).text(value));
        });

        $entrySet.append($("<option>", { "data-action" : "other" }).text("Outro"));

        $modelSelector.removeAttr("disabled");
        $optGroup.append($entrySet.children());

        if (activeModel !== undefined) {
            $modelSelector.val(activeModel);

            if ($modelSelector.val() === activeModel) {
                return;
            }

            $optGroup.append($("<option>", { value : activeModel }).text(activeModel));
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

    return exports;
});
