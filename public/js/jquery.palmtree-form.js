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
}(function ($) {
    'use strict';

    $(function () {
        $('.palmtree-form.is-ajax').palmtreeForm();
    });

    var pluginName = 'palmtreeForm';

    function Plugin(element, options) {
        this.$form = $(element);
        this.$submitButton = this.$form.find('[type=submit]').last();
        this.options = $.extend({}, $.fn[pluginName].defaults, options);

        this.init(this);
    }

    var publicAPI = {
        setFormGroupState: function ($formGroups, state) {
            var _this = this;
            $formGroups.each(function () {
                var $formControl = $(this).find('.form-control'),
                    $feedback    = $(this).find('.form-control-feedback');

                // Remove all states first.
                for (var i = 0; i < _this.options.controlStates.length; i++) {
                    $(this).removeClass('has-' + _this.options.controlStates[i]);
                    $formControl.removeClass('form-control-' + _this.options.controlStates[i]);
                }

                if (!state) {
                    $feedback.hide();
                } else if ($.inArray(state, _this.options.controlStates) > -1) {
                    $(this).addClass('has-' + state);
                    $formControl.addClass('form-control-' + state);
                    $feedback.show();
                }

                _this.$form.trigger(_this.getEvent('formGroupStateChange', {
                    '$formGroup': $(this),
                    state:        state
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

            _this.$form.on('submit.palmtreeForm', function (event) {
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
                url:      $form.attr('action') || _this.options.url,
                type:     _this.$form.prop('method') || _this.options.method,
                dataType: _this.options.dataType,
                data:     $form.serialize(),
                context:  _this
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

            var $formControls = _this.$form.find('.form-control');

            // Clear all form group states
            _this.setFormGroupState($formControls.closest('.form-group'), '');

            if (response.success) {
                $formControls.filter(':visible').val('');

                if (response.data.message) {
                    _this.showAlert(response.data.message, 'success');
                }

                if (_this.options.removeSubmitButton) {
                    _this.$submitButton.remove();
                }

                return true;
            }

            // If we got here then there are errors.
            var errors = response.data.errors || null;

            _this.setControlParentStates($formControls, errors);

            $formControls.filter('.form-control-danger').first().focus();

            if (response.data.message) {
                _this.showAlert(response.data.message);
            }

            return false;
        },

        setControlParentStates: function ($formControls, errors) {
            var _this = this;

            $formControls.each(function () {
                var errorKey   = $(this).data('name'),
                    $formGroup = $(this).closest('.form-group'),
                    $feedback  = $formGroup.find('.form-control-feedback');

                if (errors && errorKey && typeof errors[errorKey] !== 'undefined') {
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
                content:  content,
                type:     type,
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
        url:                '',
        method:             'GET',
        dataType:           'json',
        removeSubmitButton: true,
        controlStates:      ['danger', 'success', 'warning']
    };

    return $.fn[pluginName];

}));
