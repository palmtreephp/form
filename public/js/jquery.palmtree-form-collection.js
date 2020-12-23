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
