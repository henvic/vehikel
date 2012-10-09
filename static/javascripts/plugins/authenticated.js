/*global define, window, AppParams */
/*jshint indent:4 */

define(["jquery"], function ($) {
    "use strict";

    /**
     * Logout routine for the logout button on the navbar
     *
     * This overrides the default action which is loading
     * the /logout page in which the user can do remote logout
     * and see its recent activities
     */
    var navbarLogoutClosure = (function () {
        var logoutAnchor = $("#navbar-logout-link");
        logoutAnchor.click(function (e) {
            e.preventDefault();

            var params = { "hash" : AppParams.global_auth_hash, "signout" : "true" };
            var form = document.createElement("form");
            document.body.appendChild(form);
            form.setAttribute("method", "POST");
            form.setAttribute("action", "/logout");

            for (var key in params) {
                if (params.hasOwnProperty(key))
                {
                    var hiddenField = document.createElement("input");
                    hiddenField.setAttribute("type", "hidden");
                    hiddenField.setAttribute("name", key);
                    hiddenField.setAttribute("value", params[key]);
                    form.appendChild(hiddenField);
                }
            }
            form.submit();
        });
    } ());

    return function () {};
});
