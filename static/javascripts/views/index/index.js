/*global define, require */
/*jslint browser: true */

define(['AppParams', 'jquery', 'models/vehicles'], function (AppParams, $, vehiclesModel) {
    'use strict';

    var $indexContent = $('#index-content'),
        $searchText = $('#search-text'),
        $advancedSearchType = $('#advanced-search-type'),
        $advancedSearchMake = $('#advanced-search-make'),
        $advancedSearchModel = $('#advanced-search-model'),
        $postNewAdButtonOnMainPage = $('#post-new-ad-button-on-main-page'),
        $postNewAdButton = $('#post-new-ad-button'),
        closeIndexContent;

    vehiclesModel.setUp($advancedSearchType, $advancedSearchMake, $advancedSearchModel);

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

    if (AppParams.selfUid) {
        $postNewAdButtonOnMainPage.on('click', function (e) {
            $postNewAdButton.click();
            e.preventDefault();
        });
    }
});
