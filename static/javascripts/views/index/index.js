/*global define, require */
/*jslint browser: true */

define(['jquery'], function ($) {
    'use strict';

    var $indexContent = $('#index-content'),
        $searchText = $('#search-text'),
        closeIndexContent;

    closeIndexContent = function () {
        if ($indexContent === undefined) {
            return;
        }

        $indexContent.stop(true, true)
            .animate({
                height: 'toggle',
                opacity: 'toggle'
            }, 700);

        setTimeout(function () {
            $indexContent.remove();
            $indexContent = undefined;
        }, 800);
    };

    $searchText.one('keyup', function () {
        closeIndexContent();
    });

    $searchText.one('change', function () {
        closeIndexContent();
    });

    $searchText.one('paste', function () {
        closeIndexContent();
    });
});
