/**
 * jquery-bsalert v0.9.4
 *
 * @author Andy Palmer <andy@andypalmer.me>
 * @license MIT
 */
(function (factory) {
	// Universal Module Definition
	/* jshint strict: false */
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as an anonymous module.
		define(['jquery'], factory);
	} else if (typeof module === 'object' && module.exports) {
		// Node/CommonJS
		module.exports = factory;
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {
	/* jshint unused: vars */

	'use strict';

	var pluginName = 'bsAlert';

	var publicAPI = {

		destroy: function () {
			this.clear();

			this.$el.removeData(pluginName);
		},

		show: function () {
			if ($.isFunction(this.options.position)) {
				this.options.position.call(this, this.getAlert());
			} else {
				switch (this.options.position) {
					case 'after':
						this.$el.after(this.getAlert());
						break;
					default:
						this.$el.before(this.getAlert());
						break;

				}
			}
		},

		clear: function () {
			var instances = this.$el.data(pluginName) || [];

			for (var i = 0; i < instances.length; i++) {
				instances[i].$alert.remove();
			}
		}
	};

	var privateAPI = {

		init: function () {
			if (this.options.clear) {
				this.clear();
			}

			this.show();
		},

		getAlert: function () {
			var $alert = $('<div />');

			$alert
				.attr('role', 'alert')
				.addClass('alert alert-' + this.options.type)
				.append(' ' + this.getContent(this.options.content));

			if (this.options.icons && this.options.icons[this.options.type]) {
				var $icon = $('<span />').addClass(this.options.icons[this.options.type]);

				$alert.prepend($icon);
			}

			if (this.options.dismissible) {
				$alert.addClass('alert-dismissible');

				$alert.append($('<button />').attr({
					'type': 'button',
					'data-dismiss': 'alert',
					'aria-label': 'Close',
					'class': 'close'
				}).html('<span aria-hidden="true">&times;</span>'));
			}

			this.$alert = $alert;

			return $alert;
		},

		getContent: function (arg) {
			var _this   = this,
				content = '';

			switch (typeof arg) {
				case 'function':
					content = arg.call(_this);
					break;
				case 'object':
					$.each(arg, function (i, part) {
						content += this.getContent(part);
					});
					break;
				default:
					content = arg;
					break;
			}

			return content;
		}
	};

	function Plugin(element, options) {
		this.$el = $(element);
		this.$alert = null;
		this.options = $.extend({}, $.fn[pluginName].defaults, options);

		this.init();
	}

	Plugin.prototype = $.extend({}, publicAPI, privateAPI);

	$.fn[pluginName] = function () {
		var args = arguments;

		return this.each(function () {
			if (args.length === 2 && typeof args[0] === 'string' && typeof args[1] === 'string') {
				args[0] = {
					type: args[0],
					content: args[1]
				};
			}

			var instances = $(this).data(pluginName) || [];

			instances.push(new Plugin(this, args[0]));

			$(this).data(pluginName, instances);

			if (typeof args[0] === 'string' && $.isFunction(publicAPI[args[0]])) {
				publicAPI[args[0]].apply(instances[0], Array.prototype.slice.call(args, 1));
			}
		});
	};

	$.fn[pluginName].defaults = {
		type: 'danger', // danger, warning, info, success
		content: '',
		clear: true,
		dismissible: false,
		position: 'default',
		icons: {
			danger: 'fa fa-exclamation-circle',
			warning: 'fa fa-question-circle',
			info: 'fa fa-info-circle',
			success: 'fa fa-check-circle'
		}
	};

	//noinspection JSAnnotator
	return $.fn[pluginName];

}) );

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
    // Universal Module Definition
    /* jshint strict: false */
    if (typeof module === 'object' && module.exports) {
        // Node/CommonJS (Browserify/Webpack)
        module.exports = factory;
    } else if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function ($) {
    'use strict';

    var pluginName = 'palmtreeFormCollection';

    function Plugin(element, options) {
        this.$collection = $(element);
        this.options = $.extend(true, {}, $.fn[pluginName].defaults, options);

        this.init();
    }

    Plugin.prototype = {
        init: function () {
            var _this = this;

            this.$collection.data('index', this.$collection.find(this.options.entrySelector).length);

            console.log(this.$collection);

            this.$collection.find(this.options.entrySelector).each(function () {
                console.log($(this));
                _this.addRemoveLink($(this));
            });

            this.$collection.on('click', '.remove-entry-link', function (e) {
                e.preventDefault();
                e.stopPropagation();

                _this.removeEntry($(this).closest('.palmtree-form-collection-entry').parent());
            });

            this.$addEntryLink = $('<button type="button" class="add-entry-link btn btn-primary">' + this.options.labels.add + '</button>');

            this.$collection.after(this.$addEntryLink);

            this.$addEntryLink.on('click', function (e) {
                e.preventDefault();
                _this.addEntry();
            });

            if (typeof this.options.minEntries === 'number') {
                for (var i = 0; i <= this.options.minEntries; i++) {
                    _this.addEntry();
                }
            }

            if (this.hasMaxEntries()) {
                this.$addEntryLink.addClass('disabled');
            }
        },

        addEntry: function () {
            if (this.hasMaxEntries()) {
                return false;
            }

            var index = this.$collection.data('index'),
                prototype;

            if (this.options.prototype) {
                prototype = this.options.prototype;
            } else {
                prototype = this.$collection.data('prototype');
                prototype = prototype.replace(/__name__label__/g, index).replace(/__name__/g, index)
            }

            var $entry = $(prototype);

            $entry.data('index', this.$collection.data('index'));

            this.addRemoveLink($entry);

            this.$collection.append($entry);

            this.$collection.data('index', index + 1);

            this.$collection.trigger('addedEntry.palmtreeFormCollection', [$entry, this]);

            if (this.hasMaxEntries()) {
                this.$addEntryLink.addClass('disabled')
            }
        },

        removeEntry: function ($entry) {
            if (typeof this.options.minEntries === 'number' && this.options.minEntries === this.getTotalEntries()) {
                return false;
            }

            this.$collection.triggerHandler('removeEntry.palmtreeFormCollection', [$entry, this]);

            var _this = this;
            promise.then(function (proceed) {
                if (proceed) {
                    $entry.remove();

                    _this.$collection.triggerHandler('removedEntry.palmtreeFormCollection', [_this]);

                    if (!_this.hasMaxEntries()) {
                        _this.$addEntryLink.removeClass('disabled')
                    }
                }
            });
        },

        addRemoveLink: function ($entry) {
            $entry.append('<button type="button" class="remove-entry-link btn btn-sm btn-danger">' + this.options.labels.remove + '</button>');
        },

        /**
         *
         * @returns {number}
         */
        getTotalEntries: function () {
            return this.$collection.find(this.options.entrySelector).length;
        },

        /**
         *
         * @returns {boolean}
         */
        hasMaxEntries: function () {
            return typeof this.options.maxEntries === 'number' && this.options.maxEntries === this.getTotalEntries();
        }
    };

    $.fn[pluginName] = function (options) {
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
        entrySelector: '> div > .palmtree-form-collection-entry',
        prototype: null,
        labels: {
            add: 'Add Entry',
            remove: 'Remove'
        },
        minEntries: null,
        maxEntries: null,
        removalPromise: false,
        confirmRemove: false
    };
}));

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
