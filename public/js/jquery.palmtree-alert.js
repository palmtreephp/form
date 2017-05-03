(function (factory) {
    'use strict';
    /* global define:false */
    if (typeof define !== 'undefined' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory;
    } else {
        factory(window.jQuery);
    }
}(function ($) {

    'use strict';

    var pluginName = 'palmtreeAlert';

    function Plugin(element, options) {
        this.$el = $(element);
        this.$alert = null;
        this.options = $.extend({}, $.fn[pluginName].defaults, options);

        this._init();
    }

    Plugin.prototype = {
        /**
         * Initialises the plugin instance.
         */
        _init: function () {
            this.$alert = this._getElement();

            switch (this.options.position) {
                case 'before':
                    this.$el.before(this.$alert);
                    break;
                default:
                    this.$el.find('input[type=submit], button[type=submit]').last().before(this.$alert);
                    break;

            }

        },
        destroy: function () {
            this.$alert.remove();

            this.$el.removeData(pluginName + '.plugin');
        },
        _getElement: function () {
            if (this.$alert !== null) {
                this.$alert.remove();
            }

            var $element = $('<div />').addClass('alert').attr('role', 'alert');

            $element.addClass('alert-' + this.options.type);

            $element.append(this._getContent(this.options.content));

            if (this.options.dismissible) {
                $element.addClass('alert-dismissible');

                $element.append($('<button />').attr({
                    'type': 'button',
                    'data-dismiss': 'alert',
                    'aria-label': 'Close',
                    'class': 'close'
                }).html('<span aria-hidden="true">&times;</span>'));
            }

            this.$alert = $element;

            return $element;
        },
        _getContent: function (input) {
            var content = '';
            switch (typeof input) {
                case 'function':
                    content = input.call(this, this.options);
                    break;
                case 'object':
                    $.each(input, function (i, part) {
                        content += this.getContent(part);
                    });
                    break;
                default:
                    content = input;
                    break;
            }

            return content;
        }
    };

    $.fn[pluginName] = function () {
        var args = arguments;

        return this.each(function () {
            var plugin = $(this).data(pluginName + '.plugin');
            if (!plugin) {
                plugin = new Plugin(this, args[0]);
                $(this).data(pluginName + '.plugin', plugin);
            }

            if (typeof args[0] === 'string' && args[0].charAt(0) !== '_' && $.isFunction(plugin[args[0]])) {
                plugin[args[0]].apply(plugin, [].slice.call(args, 1));
            }
        });
    };

    $.fn[pluginName].defaults = {
        type: 'danger',
        content: '',
        dismissible: false,
        position: 'default'
    };

}) );
