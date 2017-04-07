(function ($) {

    var i18n = eddFields.i18n || {};
    var widget_selector = '[id*="edd_fields_widget"]';

    var getFieldsForDownload = function (postID, $form) {

        var $select = $form.find('.edd-fields-widget-field');

        $select.html('<option>' + i18n['loading'] + '</option>');

        $.ajax({
            'type': 'POST',
            'url': ajaxurl,
            'data': {
                'action': 'get_edd_fields_widget_field',
                'post_id': postID,
            },
            success: function (response) {

                var $fields = $form.find('.edd-fields-widget-field'),
                    $prefix = $form.find('.edd-fields-widget-prefix');

                $select.prop('selected', false).html('');

                if ( response.data.length > 1 ) {

                    $select.append('<option>' + i18n['selectField'] + '</option>');

                    for ( var i = 0; i < response.data.length; i++ ) {

                        var value = response.data[i];

                        $select.append('<option value="' + value + '">' + value + '</option>');

                    }

                } else {

                    $select.append('<option>' + i18n['noFields'] + '</option>');

                }

                var $field    = $fields.find('option:selected'),
                    fieldText = $field.text();

                if ( $field.val() == '0' ) {
                    fieldText = $prefix.data('default');
                }

                $prefix.attr('placeholder', fieldText + ': ');

            },
            error: function (request, status, error) {

            }
        });

    }

    var initializeChosenFields = function ($widget) {

        var $field = $widget.find('.edd-fields-widget-post-id');

        if ( !$field.length ) {

            return;
        }

        $field.chosen({
            inherit_select_classes: true,
        });
    }

    // When the Shortcode type changes
    $(document).on('change', '.edd-fields-widget-form .edd-fields-widget-shortcode', function () {

        if ( $(this).val() == 'individual' ) {

            var $form  = $(this).closest('.edd-fields-widget-form'),
                postID = $form.find('.edd-fields-widget-post-id').val();

            getFieldsForDownload(postID, $form);

            $form.find('.edd-fields-individual-options').removeClass('hidden');

        }
        else {
            $(this).closest('.edd-fields-widget-form').find('.edd-fields-individual-options').addClass('hidden');
        }

    });

    // When the Selected Post changes
    $(document).on('change', '.edd-fields-widget-form .edd-fields-widget-post-id', function () {

        var $form           = $(this).closest('.edd-fields-widget-form'),
            postID          = $(this).val(),
            shortcodeToggle = $form.find('.edd-fields-widget-shortcode:checked').val();

        $form.find('.edd-fields-widget-field').prop('selected', false);

        // We only need to do AJAX if we're set to Individual, as changing to Individual will trigger it anyway
        if ( shortcodeToggle == 'individual' ) {

            getFieldsForDownload(postID, $form);

        }

    });

    // When the Selected Field Changes
    $(document).on('change', '.edd-fields-widget-form .edd-fields-widget-field', function () {

        var $form     = $(this).closest('.edd-fields-widget-form'),
            $field    = $(this).find('option:selected'),
            fieldText = $field.text(),
            $prefix   = $form.find('.edd-fields-widget-prefix');

        $(this).attr('data-selected', $(this).val());

        if ( $field.val() == '0' ) {
            fieldText = $prefix.data('default');
        }

        $prefix.attr('placeholder', fieldText + ': ');

    });

    // Initialize the widgets on adding/updating
    $(document).on('widget-updated widget-added', function (e, $widget) {

        // Make sure it's the EDD Fields widget
        if (!$widget.filter(widget_selector).length) {

            return;
        }

        initializeChosenFields($widget);
    });

    // Initialize the widgets on page load
    var $widgets = $(widget_selector);

    if ($widgets.length) {

        initializeChosenFields($widgets);
    }

})(jQuery);