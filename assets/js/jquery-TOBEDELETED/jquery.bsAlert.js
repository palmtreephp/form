/**
 * jquery-bsalert v2.0.0
 *
 * @author Andy Palmer <andy@andypalmer.me>
 * @license MIT
 */
(function (factory) {
    // Universal Module Definition
    /* jshint strict: false */
    if (typeof define === "function" && define.amd) {
        // AMD. Register as an anonymous module.
        define(["jquery"], factory);
    } else if (typeof module === "object" && module.exports) {
        // Node/CommonJS
        module.exports = factory(require("jquery"));
    } else {
        // Browser globals
        factory(jQuery);
    }
})(function ($) {
    /* jshint unused: vars */
    "use strict";

    var pluginName = "bsAlert";

    var instances = {};
    var instanceIdCounter = -1;

    var publicAPI = {
        destroy: function () {
            this.clear();
            var instanceId = this.$el.data(pluginName + ".id");
            this.$el.removeData(pluginName + ".id");
            delete instances[instanceId];
        },

        show: function () {
            if ($.isFunction(this.options.position)) {
                this.options.position.call(this, this.getAlert());
            } else if (this.options.position === "after") {
                this.$el.after(this.getAlert());
            } else {
                this.$el.before(this.getAlert());
            }
        },

        clear: function () {
            instances[this.$el.data(pluginName + ".id")].$alert.remove();
        }
    };

    var privateAPI = {
        getAlert: function () {
            var $alert = $("<div />")
                .attr("role", "alert")
                .addClass("alert alert-" + this.options.type)
                .append(document.createTextNode(" " + this.getContent(this.options.content)));

            if (this.options.icons && this.options.icons[this.options.type]) {
                $alert.prepend($("<span />").addClass(this.options.icons[this.options.type]));
            }

            if (this.options.dismissible) {
                $alert.addClass("alert-dismissible").append(
                    $("<button />")
                        .attr({
                            type: "button",
                            "data-dismiss": "alert",
                            "aria-label": "Close",
                            class: "close"
                        })
                        .html('<span aria-hidden="true">&times;</span>')
                );
            }

            this.$alert = $alert;

            return $alert;
        },

        getContent: function (arg) {
            return $.isFunction(arg) ? arg.call(this) : arg;
        }
    };

    function Plugin($element, options, instanceId) {
        $element.data(pluginName + ".id", instanceId);
        this.$el = $element;
        this.$alert = null;
        this.options = $.extend({}, $.fn[pluginName].defaults, options);

        this.show();
    }

    Plugin.prototype = $.extend({}, publicAPI, privateAPI);

    $.fn[pluginName] = function () {
        var args = arguments;

        var instanceId = this.data(pluginName + ".id");

        if (typeof instanceId === "undefined") {
            instanceId = ++instanceIdCounter;
        }

        if (instances.hasOwnProperty(instanceId)) {
            if (typeof args[0] === "string" && $.isFunction(publicAPI[args[0]])) {
                return publicAPI[args[0]].apply(instances[instanceId], Array.prototype.slice.call(args, 1));
            }

            instances[instanceId].destroy();
        }

        if (typeof args[0] !== "object") {
            args[0] = { type: args[0], content: args[1] };
        }

        instances[instanceId] = new Plugin(this, args[0], instanceId);
    };

    $.fn[pluginName].defaults = {
        type: "danger", // one of danger, warning, info or success
        content: "",
        dismissible: false,
        position: "before",
        icons: {
            danger: "fa fa-exclamation-circle",
            warning: "fa fa-question-circle",
            info: "fa fa-info-circle",
            success: "fa fa-check-circle"
        }
    };

    // noinspection JSAnnotator
    return $.fn[pluginName];

});
