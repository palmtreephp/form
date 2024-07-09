(function (factory) {
    'use strict';
    /* global define:false */
    if (typeof define !== 'undefined' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports && typeof require === 'function') {
        module.exports = factory(require('jquery'));
    } else {
        factory(jQuery);
    }
})(function ($) {
    'use strict';

    $(function () {
        $('.palmtree-form').each(function () {
            if ($(this).hasClass('is-ajax')) {
                $(this).palmtreeForm();
            }
        });
    });

    var pluginName = 'palmtreeForm';

    /**
     * @param {Element} element
     * @param {{}} options
     * @constructor
     */
    function Plugin(element, options) {
        this.$form = $(element);
        this.$submitButton = this.$form.find('[type=submit]').last();
        this.options = $.extend({}, $.fn[pluginName].defaults, options);

        var _this = this;

        this.$form.on('submit.palmtreeForm', function (event) {
            event.preventDefault();
            _this.onSubmit();
        });
    }

    var publicAPI = {
        /**
         * @param {jQuery} $formControls
         */
        clearState: function ($formControls) {
            this.setState($formControls, '');
        },
        /**
         * @param {jQuery} $formControls
         * @param {string} state
         */
        setState: function ($formControls, state) {
            var _this = this;
            $formControls.each(function () {
                var $formControl = $(this),
                    $formGroup = $(this).closest('.form-group'),
                    $feedback = $formGroup.find('.palmtree-invalid-feedback');

                // Remove all states first.
                for (var i = 0; i < _this.options.controlStates.length; i++) {
                    $formControl.removeClass('is-' + _this.options.controlStates[i]);
                }

                if (!state) {
                    $feedback.hide();
                } else if (_this.options.controlStates.indexOf(state) > -1) {
                    $formControl.addClass('is-' + state);
                    $feedback.show();
                }

                _this.$form.trigger(_this.getEvent('statechange'), {
                    $formControl: $formControl,
                    state: state
                });
            });
        }
    };

    var privateAPI = {
        /**
         * Handler for the form element's submit event.
         */
        onSubmit: function () {
            var _this = this,
                $form = this.$form,
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

            promise.done(_this.handleResponse).always(function () {
                $form.removeClass('is-submitting');
                $submitButton.removeClass('disabled').prop('disabled', false);
            });

            $form.trigger(this.getEvent('promise'), {
                promise: promise
            });
        },

        /**
         * @param {{data: {}, message: string, errors: {}, success: boolean}} response
         *
         * @returns {boolean}
         */
        handleResponse: function (response) {
            var _this = this;

            if (!response || typeof response.data === 'undefined') {
                throw new Error('Invalid response');
            }

            var $formControls = _this.$form.find('.palmtree-form-control');

            // Clear all form control states
            _this.clearState($formControls);

            if (!response.success) {
                var errors = response.data.errors || null;

                _this.setControlStates($formControls, errors);

                var $first = $formControls.filter('.is-invalid').first();
                $first.trigger('focus');

                if (response.data.message) {
                    _this.showAlert(response.data.message, 'danger');
                }

                _this.$form.trigger(this.getEvent('error'), {
                    responseData: response.data
                });

                return false;
            }

            $formControls.filter(':visible').val('');

            if (response.data.message) {
                _this.showAlert(response.data.message, 'success');
            }

            if (_this.options.removeSubmitButton) {
                _this.$submitButton.remove();
            }

            _this.$form.trigger(this.getEvent('success'), {
                responseData: response.data
            });

            return true;
        },

        /**
         * @param {jQuery} $formControls
         * @param {{}} errors
         */
        setControlStates: function ($formControls, errors) {
            var _this = this;

            $formControls.each(function () {
                var $formControl = $(this),
                    errorKey = $formControl.data('name'),
                    $formGroup = $formControl.closest('.form-group'),
                    $feedback = $formGroup.find('.palmtree-invalid-feedback');

                if (errors && errorKey && typeof errors[errorKey] !== 'undefined') {
                    if (!$feedback.length) {
                        $feedback = $(_this.$form.data('invalid_element'));
                    }

                    $feedback.html(errors[errorKey]);
                    $formGroup.append($feedback);

                    _this.setState($formControl, 'invalid');

                    $(this)
                        .off('input.palmtreeForm change.palmtreeForm')
                        .on('input.palmtreeForm change.palmtreeForm', function () {
                            var state = $(this).val().length ? '' : 'invalid';
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
         * @param {...{}} props Optional properties to add to the event object.
         * @returns {jQuery.Event}
         */
        getEvent: function (eventType, props) {
            var event = new $.Event(eventType, props);
            event.namespace = pluginName;
            event.form = this;

            return event;
        },

        /**
         * @param {string} content
         * @param {string} type
         */
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

        if (args[0] === 'isInitialized') {
            return !!$(this).data(pluginName);
        }

        return this.each(function () {
            var plugin = $(this).data(pluginName);
            if (!plugin) {
                plugin = new Plugin(this, args[0]);
                $(this).data(pluginName, plugin);
            }

            if (typeof args[0] === 'string' && $.isFunction(publicAPI[args[0]])) {
                plugin[args[0]].apply(plugin, Array.prototype.slice.call(args, 1));
            }
        });
    };

    $.fn[pluginName].defaults = {
        url: '',
        method: 'GET',
        dataType: 'json',
        removeSubmitButton: true,
        controlStates: ['valid', 'invalid']
    };

    return $.fn[pluginName];
});
