(function ($) {
    'use strict';

    function submissionFormTemplateSetup() {

        var $template_select = $('#edd_fields_template');

        if (!$template_select.length) {

            return;
        }

        $template_select.change(submissionFormTemplateHandle);
        $template_select.each(submissionFormTemplateHandle);
    }

    function submissionFormTemplateHandle() {

        var template = $(this).val();
        var $templates = $('.edd-fields-template:not(#edd-fields-' + template + ')');
        var $template = $('#edd-fields-' + template);

        if ($template.length) {

            $templates.hide();
            $template.show();
        }
    }

    $(function () {

        submissionFormTemplateSetup();
    });
})(jQuery);