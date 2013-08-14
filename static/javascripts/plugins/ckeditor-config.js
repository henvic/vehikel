/*global define, CKEDITOR */
/*jshint indent:4 */

define(["ckeditor"], function () {
    "use strict";

    //open a simpler link dialog
    CKEDITOR.on("dialogDefinition", function(ev) {
        var dialogName = ev.data.name;
        var dialogDefinition = ev.data.definition;

        if (dialogName === "link") {
            dialogDefinition.removeContents("target");
            dialogDefinition.removeContents("advanced");

            var infoTab = dialogDefinition.getContents("info");

            infoTab.remove("linkType");
            infoTab.remove("protocol");
        }
    });

    var config = {};

    config.customConfig = "";
    config.language = "pt_BR";
    config.height = "400px";
    config.allowedContent = "p b i br u s; a[!href]";
    config.entities = false;
    config.basicEntities = true;
    config.entities_greek = false;
    config.entities_latin = false;
    config.entities_additional = "";
    config.htmlEncodeOutput = false;
    config.removeButtons = "Anchor,Subscript,Superscript";

    config.toolbar = [
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-'] },
        { name: 'links', items: [ 'Link', 'Unlink'] },
        { name: 'clipboard', items : [ 'Undo','Redo' ] }
    ];

    config.toolbarGroups = [
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'links' }
    ];

    return config;
});
