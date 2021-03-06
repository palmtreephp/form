(function (factory) {
    'use strict';
    /* global define:false */
    if (typeof define !== 'undefined' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory(require('jquery'));
    } else {
        factory(window.jQuery);
    }
})(function ($) {
    'use strict';

    $(function () {
        $('.palmtree-form').each(function () {
            $(this).find('.g-recaptcha').palmtreeRecaptcha(this);
        });
    });

    var pluginName = 'palmtreeRecaptcha';

    /**
     *
     * @param {HTMLElement} element
     * @param {HTMLElement} form
     * @constructor
     */
    function Plugin(element, form) {
        this.$el = $(element);
        this.$form = $(form);

        var _this = this;

        window[this.$el.data('onload')] = function () {
            var widgetId = window.grecaptcha.render(_this.$el.attr('id'), {
                sitekey: _this.$el.data('site_key'),
                callback: function (response) {
                    var $formControl = $('#' + _this.$el.data('form_control'));
                    $formControl.val(response);
                    if (_this.$form.palmtreeForm('isInitialized')) {
                        _this.$form.palmtreeForm('clearState', $formControl);
                    }
                }
            });

            _this.$form.on('error.palmtreeForm success.palmtreeForm', function () {
                window.grecaptcha.reset(widgetId);
            });
        };

        if (this.$el.data('autoload')) {
            $.getScript(this.$el.data('script_url'));
        }
    }

    $.fn[pluginName] = function (form) {
        return this.each(function () {
            if (!$(this).data(pluginName)) {
                $(this).data(pluginName, new Plugin(this, form));
            }
        });
    };

    return $.fn[pluginName];
});
