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
