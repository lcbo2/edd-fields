(function ($) {
    'use strict';

    var i18n = eddFieldsFES.i18n || {};

    // This oneInstance thing has to be replicated because the list isn't filterable...
    $(function () {

        $('.fes-form-buttons button[data-name="edd_fields"]').click(preventDuplicateFields);

        function preventDuplicateFields(e) {

            var $formEditor = $('ul#fes-formbuilder-fields');

            if ( $formEditor.find('li.edd_fields').length ) {

                alert(i18n['fesFormBuilderDuplicateFields']);
                e.stopImmediatePropagation();
                e.preventDefault();
            }
        }
    });
})(jQuery);