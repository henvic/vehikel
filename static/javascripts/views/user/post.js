/*global define */
/*jshint indent:4 */

define(['jquery'], function ($) {
    "use strict";

    var postInfoTabsLinks = $('#post-info-tabs a');

    postInfoTabsLinks.click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    var postContactDiv = $('#post-contact');

    postContactDiv.on("submit", function (e) {
        var target = $(e.target);
        if (target.is('form')) {
            e.preventDefault();

            var submit = $('input[type="submit"]', postContactDiv);

            submit.data('loading-text', 'Enviando...').button('loading');

            $.ajax({
                type: 'POST',
                data: target.serialize(),
                success: function (result) {
                    postContactDiv.html(result);
                },
                complete: function (result) {
                    submit.button('reset');
                }
            });
        }
    });

    postContactDiv.on("click", function (e) {
        var target = $(e.target);
        if (target.is('.close')) {
            $.ajax({
                type: 'GET',
                success: function (result) {
                    postContactDiv.html(result);
                }
            });
        }
    });
});
