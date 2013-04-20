/*global define, CKEDITOR */
/*jshint indent:4 */

define(['AppParams', 'jquery', 'plugins/ckeditor-config'],
    function (AppParams, $, ckeditorConfig) {
        "use strict";

        var $postTemplateForm = $("#post-template-form");
        var $postTemplate = $("#post_template");

        var editor;

        CKEDITOR.on("instanceReady", function (ev) {
            editor = CKEDITOR.instances.post_template;

            $postTemplateForm.on("submit", function (e) {
                $postTemplate.val(editor.getData());
            });
        });

        CKEDITOR.replace("post_template", ckeditorConfig);
    }
);
