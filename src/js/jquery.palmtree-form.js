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
            if ($(this).hasClass('is-ajax')) {
                $(this).palmtreeForm();
            }
        });
    });

    var pluginName = 'palmtreeForm';

    function Plugin(element, options) {
        this.$form = $(element);
        this.$submitButton = this.$form.find('[type=submit]').last();
        this.options = $.extend({}, $.fn[pluginName].defaults, options);

        this.init();
    }

    var publicAPI = {
        clearState: function ($formControls) {
            this.setState($formControls, '');
        },
        setState: function ($formControls, state) {
            var _this = this;
            $formControls.each(function () {
                var $formControl = $(this),
                    $formGroup   = $(this).closest('.form-group'),
                    $feedback    = $formGroup.find('.invalid-feedback');

                // Remove all states first.
                for (var i = 0; i < _this.options.controlStates.length; i++) {
                    $formControl.removeClass('is-' + _this.options.controlStates[i]);
                }

                if (!state) {
                    $feedback.hide();
                } else if ($.inArray(state, _this.options.controlStates) > -1) {
                    $formControl.addClass('is-' + state);
                    $feedback.show();
                }

                _this.$form.trigger(_this.getEvent('statechange', {
                    '$formControl': $formControl,
                    state: state
                }));
            });
        }
    };

    var privateAPI = {
        /**
         * Initialises the plugin instance.
         */
        init: function () {
            var _this = this;

            this.$form.on('submit.palmtreeForm', function (event) {
                event.preventDefault();
                _this.onSubmit();
            });
        },

        /**
         * Handler for the form element's submit event.
         */
        onSubmit: function () {
            var _this         = this,
                $form         = this.$form,
                $submitButton = this.$submitButton;

            $form.addClass('is-submitting');
            $submitButton.addClass('disabled').prop('disabled', true);

            $form.trigger(this.getEvent('beforeSend'));

            var promise = $.ajax({
                url: $form.attr('action') || _this.options.url,
                type: _this.$form.prop('method') || _this.options.method,
                dataType: _this.options.dataType,
                data: $form.serialize(),
                context: _this
            });

            promise
                .done(_this.handleResponse)
                .always(function () {
                    $form.removeClass('is-submitting');
                    $submitButton.removeClass('disabled').prop('disabled', false);
                });

            $form.trigger(this.getEvent('promise', {
                promise: promise
            }));
        },

        /**
         *
         * @param {object} response
         *
         * @param {boolean} response.success
         *
         * @param {object} response.data
         * @param {string} response.data.message
         * @param {object} response.data.errors
         *
         * @returns {boolean}
         */
        handleResponse: function (response) {
            var _this = this;

            if (!response || typeof response.data === 'undefined') {
                $.error('Invalid response');
                return false;
            }

            var $formControls = _this.$form.find('.palmtree-form-control');

            // Clear all form control states
            _this.clearState($formControls);

            if (!response.success) {
                var errors = response.data.errors || null;

                _this.setControlStates($formControls, errors);

                var $first = $formControls.filter('.is-invalid').first();
                $first.focus().closest('.form-group').find('.invalid-feedback').hide().fadeIn();

                if (response.data.message) {
                    _this.showAlert(response.data.message);
                }

                _this.$form.trigger(this.getEvent('error', {
                    responseData: response.data
                }));

                return false;
            }

            $formControls.filter(':visible').val('');

            if (response.data.message) {
                _this.showAlert(response.data.message, 'success');
            }

            if (_this.options.removeSubmitButton) {
                _this.$submitButton.remove();
            }

            _this.$form.trigger(this.getEvent('success', {
                responseData: response.data
            }));

            return true;
        },

        setControlStates: function ($formControls, errors) {
            var _this = this;

            $formControls.each(function () {
                var $formControl = $(this),
                    errorKey     = $formControl.data('name'),
                    $formGroup   = $formControl.closest('.form-group'),
                    $feedback    = $formGroup.find('.invalid-feedback');

                if (errors && errorKey && typeof errors[errorKey] !== 'undefined') {
                    if (!$feedback.length) {
                        $feedback = $('<div />').addClass('invalid-feedback small');
                    }

                    $feedback.html(errors[errorKey]);
                    $formGroup.append($feedback);

                    _this.setState($formControl, 'invalid');

                    $(this)
                        .off('input.palmtreeForm change.palmtreeForm')
                        .on('input.palmtreeForm change.palmtreeForm', function () {
                            var state = ( $(this).val().length ) ? '' : 'invalid';
                            _this.setState($formControl, state);
                        });

                } else {
                    _this.clearState($formControl);
                }
            });
        },

        /**
         * Returns a new jQuery event object with the plugin's namespace.
         *
         * @param {string} eventType The type of event e.g 'click'.
         * @param {...object} props Optional properties to add to the event object.
         * @returns {jQuery.Event}
         */
        getEvent: function (eventType, props) {
            var event = new $.Event(eventType, props);
            event.namespace = pluginName;
            event.form = this;

            return event;
        },

        showAlert: function (content, type) {
            var _this = this;
            this.$form.bsAlert({
                content: content,
                type: type,
                position: function ($alert) {
                    _this.$submitButton.before($alert);
                }
            });
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

    $.fn[pluginName].defaults = {
        url: '',
        method: 'GET',
        dataType: 'json',
        removeSubmitButton: true,
        controlStates: ['valid', 'invalid']
    };

    return $.fn[pluginName];
}));
