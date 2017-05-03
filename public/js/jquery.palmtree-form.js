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
        $('form.is-ajax').palmtreeForm();
    });

    var pluginName = 'palmtreeForm';

    function Plugin(element, options) {
        this.$form = $(element);
        this.$submitButton = this.$form.find('input[type=submit], button[type=submit]').last();
        this.options = $.extend({}, $.fn[pluginName].defaults, options);

        this.init();
    }

    Plugin.prototype = {
        /**
         * Initialises the plugin instance.
         */
        init: function () {
            var _this = this;

            this.$form.on('submit.palmtreeForm', function (event) {
                event.preventDefault();

                _this.onSubmit.call(_this);
            });
        },
        onSubmit: function () {
            var $form = this.$form,
                $submitButton = this.$submitButton;

            $form.addClass('is-submitting');
            $submitButton.addClass('disabled').prop('disabled', true);

            $.ajax({
                url: $form.attr('action') || this.options.url,
                type: this.getMethod(),
                dataType: this.options.dataType,
                data: $form.serialize(),
                context: this,
                success: this.handleResponse,
                complete: function () {
                    /*jshint validthis: true */
                    $form.removeClass('is-submitting');
                    $submitButton.removeClass('disabled').prop('disabled', false);
                   // $form.unblock();

                    if ($.isFunction(this.options.onComplete)) {
                        this.options.onComplete.call(this, this.options);
                    }
                }
            });
        },
        getMethod: function () {
            return this.$form.prop('method') || this.options.method;
        },
        handleResponse: function (response) {
            if (!response || typeof response.data === 'undefined') {
                // todo: error checking
                return false;
            }

            var $formGroups = this.$form.find('.form-group'),
                $formControls = this.$form.find('.form-control');

            // Clear all form group states
            this.setFormGroupState($formGroups, '');

            if (response.success) {
                $formControls.filter(':visible').val('');

                if (response.data.message) {
                    this.$form.palmtreeAlert('destroy').palmtreeAlert({
                        type: 'success',
                        content: response.data.message
                    });
                }

                this.$submitButton.remove();

                return true;
            }

            var _this = this;

            // If we got here then there are errors.
            var errors = response.data.errors || {};

            $formControls.each(function () {
                var errorKey = $(this).data('name') || '',
                    $formGroup = $(this).closest('.form-group'),
                    $feedback = $formGroup.find('.form-control-feedback');

                if (typeof errors[errorKey] !== 'undefined') {
                    if (!$feedback.length) {
                        $feedback = $('<div />').addClass('form-control-feedback small');
                    }

                    $feedback.html(errors[errorKey]);

                    $formGroup.append($feedback);

                    _this.setFormGroupState($formGroup, 'danger');

                    $(this).off('input.palmtreeForm').on('input.palmtreeForm', function () {
                        var state = ( $(this).val().length ) ? '' : 'danger';
                        _this.setFormGroupState($formGroup, state);
                    });

                } else {
                    _this.setFormGroupState($formGroup, '');
                }
            });

            $formControls.filter('.form-control-danger').first().focus();

            if (response.data.message) {
                this.$form.palmtreeAlert('destroy').palmtreeAlert({
                    content: response.data.message
                });
            }

            return false;
        },
        setFormGroupState: function ($formGroups, state) {
            var _this = this;
            $formGroups.each(function () {
                var $formControl = $(this).find('.form-control'),
                    $feedback = $(this).find('.form-control-feedback');

                // Remove all states first.
                for (var i = 0; i < _this.options.controlStates.length; i++) {
                    $(this).removeClass('has-' + _this.options.controlStates[i]);
                    $formControl.removeClass('form-control-' + _this.options.controlStates[i]);
                }

                if (!state.length) {
                    $feedback.hide();
                } else if ($.inArray(state, _this.options.controlStates) > -1) {
                    $(this).addClass('has-' + state);
                    $formControl.addClass('form-control-' + state);
                    $feedback.show();
                }
            });
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
        });
    };

    $.fn[pluginName].defaults = {
        url: '',
        method: 'GET',
        dataType: 'json',
        onSuccess: null,
        onComplete: null,
        controlStates: ['danger', 'success', 'warning']
    };

    return $.fn[pluginName];

}));
