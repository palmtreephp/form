(function (factory) {
    'use strict';
    /* global define:false */
    if (typeof define !== 'undefined' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports && typeof require === 'function') {
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
