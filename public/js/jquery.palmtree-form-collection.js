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
