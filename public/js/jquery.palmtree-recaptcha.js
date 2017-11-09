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

    $(function () {
        $('.palmtree-form').each(function () {
            var $recaptcha = $(this).find('.g-recaptcha');

            if ($recaptcha && $.isFunction($.fn.palmtreeRecaptcha)) {
                $recaptcha.palmtreeRecaptcha(this);
            }
        });
    });

    var pluginName = 'palmtreeRecaptcha';

    function Plugin(element, form, options) {
        this.$el = $(element);
        this.$form = $(form);
        this.options = $.extend({}, $.fn[pluginName].defaults, options);

        this.init();
    }

    var publicAPI = {};

    var privateAPI = {
        init: function () {
            var _this = this;

            window[this.$el.data('onload')] = function () {
                var widgetId = window.grecaptcha.render(_this.$el.attr('id'), {
                    sitekey: _this.$el.data('site_key'),
                    callback: function (response) {
                        var $formControl = $('#' + _this.$el.data('form_control'));
                        $formControl.val(response);
                        _this.$form.palmtreeForm('clearState', $formControl);
                    }
                });

                _this.$form.on('error.palmtreeForm success.palmtreeForm', function () {
                    window.grecaptcha.reset(widgetId);
                });
            };

            $.getScript(this.$el.data('script_url'));
        }
    };

    Plugin.prototype = $.extend({}, publicAPI, privateAPI);

    $.fn[pluginName] = function () {
        var args = arguments;

        return this.each(function () {
                var plugin = $(this).data(pluginName);
                if (!plugin) {
                    plugin = new Plugin(this, args[0]);
                    $(this).data(pluginName, plugin);
                }

                if (typeof args[0] === 'string' && $.isFunction(publicAPI[args[0]])) {
                    plugin[args[0]].apply(plugin, Array.prototype.slice.call(args, 1));
                }
            }
        );
    };

    $.fn[pluginName].defaults = {};

    return $.fn[pluginName];
}));
