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
        $('.palmtree-form').on('change', '.custom-file-input', function () {
            $(this).next('.custom-file-label').html(this.files[0].name);
        });
    });
});

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
            var $recaptcha = $(this).find('.g-recaptcha');

            if ($recaptcha.length && typeof window.grecaptcha !== 'undefined') {
                $recaptcha.palmtreeRecaptcha(this);
            }
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
        this.options = $.extend({}, $.fn[pluginName].defaults, options);

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

        $.getScript(this.$el.data('script_url'));
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

/**
 * jQuery plugin to assist with adding/removing of entries
 * from a Palmtree Form CollectionType form field.
 *
 * Basic usage:  $('#collection').palmtreeFormCollection();
 * With options: $('#collection').palmtreeFormCollection({
 *                   labels: {
 *                       add: 'Add Thing',
 *                       remove: 'Remove Thing'
 *                   }
 *                });
 *
 * @author Andy Palmer
 */
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

    var pluginName = 'palmtreeFormCollection';

    /**
     * @param {Element} element
     * @param {{}} options
     * @constructor
     */
    function Plugin(element, options) {
        this.$collection = $(element);
        this.$entriesWrapper = this.$collection.children('.palmtree-form-collection-entries');
        this.options = $.extend(true, {}, $.fn[pluginName].defaults, options);

        this.$collection.data('index', this.$entriesWrapper.children().length);

        this.$addEntryLink = $('<button type="button" class="add-entry-link btn btn-primary">' + this.options.labels.add + '</button>');
        this.removeEntryLink = '<button type="button" class="remove-entry-link btn btn-sm btn-danger">' + this.options.labels.remove + '</button>';

        var _this = this;

        this.$entriesWrapper.children().each(function () {
            _this.addRemoveLink($(this));
        });

        this.$collection.on('click', '.remove-entry-link', function (e) {
            e.preventDefault();
            e.stopPropagation();

            _this.removeEntry($(this).closest('.palmtree-form-collection-entry'));
        });

        this.$collection.after(this.$addEntryLink);

        this.$addEntryLink.on('click', function (e) {
            e.preventDefault();
            _this.addEntry();
        });

        for (var i = this.getTotalEntries(); i < this.options.minEntries; i++) {
            this.addEntry();
        }

        if (this.hasMaxEntries()) {
            this.$addEntryLink.addClass('disabled');
        }
    }

    Plugin.prototype = {
        addEntry: function () {
            if (this.hasMaxEntries()) {
                return false;
            }

            var index = this.$collection.data('index'),
                prototype;

            if (this.options.prototype) {
                prototype = this.options.prototype;
            } else {
                prototype = this.$collection.data('prototype').replace(/\[-1]/g, '[' + index + ']');
                console.log(prototype);
            }

            var $entry = $(prototype);

            $entry.data('index', this.$collection.data('index'));

            this.addRemoveLink($entry);

            this.$entriesWrapper.append($entry);

            this.$collection.data('index', index + 1);

            this.$collection.trigger('addedEntry.palmtreeFormCollection', [$entry, this]);

            if (this.hasMaxEntries()) {
                this.$addEntryLink.addClass('disabled');
            }
        },

        /**
         *
         * @param {jQuery} $entry
         */
        removeEntry: function ($entry) {
            if (this.options.minEntries === this.getTotalEntries()) {
                return;
            }

            this.$collection.triggerHandler('removeEntry.palmtreeFormCollection', [$entry, this]);

            $entry.remove();

            this.$collection.triggerHandler('removedEntry.palmtreeFormCollection', [this]);

            if (!this.hasMaxEntries()) {
                this.$addEntryLink.removeClass('disabled');
            }
        },

        addRemoveLink: function ($entry) {
            $entry.append($(this.removeEntryLink));
        },

        /**
         * @returns {number}
         */
        getTotalEntries: function () {
            return this.$entriesWrapper.children().length;
        },

        /**
         * @returns {boolean}
         */
        hasMaxEntries: function () {
            return this.options.maxEntries === -1 || this.getTotalEntries() >= this.options.maxEntries;
        }
    };

    $.fn[pluginName] = function () {
        var args = arguments;
        return this.each(function () {
            var plugin = $(this).data(pluginName);

            if (!plugin) {
                plugin = new Plugin(this, args[0]);
                $(this).data(pluginName, plugin);
            }

            if (typeof args[0] === 'string') {
                plugin[args[0]].apply(plugin, Array.prototype.slice.call(args, 1));
            }
        });
    };

    $.fn[pluginName].defaults = {
        prototype: null,
        labels: {
            add: 'Add Entry',
            remove: 'Remove'
        },
        minEntries: 0,
        maxEntries: -1
    };
});

(function (factory) {
    'use strict';
    /* global define:false */
    if (typeof define !== 'undefined' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
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
