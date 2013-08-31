/*global define, CKEDITOR */
/*jslint browser: true */

define(['jquery', 'plugins/ckeditor-config'],
    function ($, ckeditorConfig) {
        'use strict';

        var $postTemplateForm = $('#post-template-form'),
            $postTemplate = $('#post_template'),
            editor;

        CKEDITOR.on('instanceReady', function () {
            editor = CKEDITOR.instances.post_template;

            $postTemplateForm.on('submit', function () {
                $postTemplate.val(editor.getData());
            });
        });

        CKEDITOR.replace('post_template', ckeditorConfig);
    });
