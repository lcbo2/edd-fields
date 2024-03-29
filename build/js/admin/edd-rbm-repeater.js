// Initialize special fields if they exist
function init_edd_rbm_repeater_colorpickers(modal) {

    var regex = /value="(#(?:[0-9a-f]{3}){1,2})"/i;

    // Only try to run if there are any Color Pickers within an EDD Repeater
    if ( jQuery(modal).find('.edd-color-picker').length ) {

        // Hit each colorpicker individually to ensure its settings are properly used
        jQuery(modal).find('.edd-color-picker').each(function (index, colorPicker) {

            // Value exists in HTML but is inaccessable via JavaScript. No idea why.
            var value = regex.exec(jQuery(colorPicker)[0].outerHTML)[1];

            jQuery(colorPicker).val(value).attr('value', value).wpColorPicker();

        });

    }

}

function init_edd_rbm_repeater_tooltips(modal) {

    jQuery(modal).find('.edd-help-tip').each(function (index, tooltip) {

        jQuery(tooltip).tooltip({
            content: function () {
                return jQuery(tooltip).prop('title');
            },
            tooltipClass: 'edd-ui-tooltip',
            position: {
                my: 'center top',
                at: 'center bottom+10',
                collision: 'flipfit',
            },
            hide: {
                duration: 200,
            },
            show: {
                duration: 200,
            },
        });

    });

}

function init_edd_rbm_repeater_options_button(modal) {

    jQuery(modal).find('.edd-fields-type').each(function (index, type) {

        if ( eddFields.showFieldsOptions.indexOf(jQuery(type).val()) > -1 ) {
            jQuery(type).closest('[data-repeater-item]').find('[data-options-repeater-edit]').attr('disabled', false);
        }
        else {
            jQuery(type).closest('[data-repeater-item]').find('[data-options-repeater-edit]').attr('disabled', true);
        }

    });

}

function init_edd_rbm_repeater_required_fields(modal) {

    jQuery(modal).find('.required').each(function (index, field) {

        jQuery(field).attr('data-validation-error', eddFields.i18n.requiredError);

        if ( jQuery(field).closest('td').hasClass('hidden') ) {
            jQuery(field).attr('required', false);
        }
        else {
            jQuery(field).attr('required', true);
        }

        // Fix Tab Ordering Bug
        if ( jQuery(field).hasClass('edd-fields-chosen') ) {

            // Ensure the Chosen Container has been built
            jQuery(field).chosen();

            // No Tab index for the "hidden" Select field
            jQuery(field).attr('tabindex', -1);

            // Why would you be unable to tab into it by default?!?!
            jQuery(field).siblings('.chosen-container').find('.chosen-single').attr('tabindex', 0);

        }

    });

}

function edd_repeater_reindex_primary() {

    jQuery('[data-edd-rbm-repeater] .edd-rbm-repeater-item').each(function (index, row) {

        var uuid   = jQuery(row).find('[data-repeater-edit]').data('open'),
            $modal = jQuery('[data-reveal="' + uuid + '"]');

        $modal.find('[name]').each(function (inputIndex, input) {

            var reindexed = jQuery(input).attr('name').replace(/\[\d+\]/, '[' + index + ']'); // Only replaces the first one as to not break Nested Repeaters

            jQuery(input).attr('name', reindexed);

        });

        init_edd_rbm_repeater_colorpickers($modal);
        init_edd_rbm_repeater_tooltips($modal);
        init_edd_rbm_repeater_required_fields($modal);
        init_edd_rbm_repeater_options_button($modal);

    });

}

// Repeaters
(function ($) {

    // This only targets the top-level, primary repeater
    var $repeaters = $('[data-edd-rbm-repeater]');

    if ( !$repeaters.length ) {
        return;
    }

    var edd_repeater_show = function () {

        // Make sure selects aren't empty!
        $(this).find('select option:eq(0)').prop('selected', true);

        // Hide current title for new item and show default title
        $(this).find('.repeater-header div.title').html($(this).find('.repeater-header div.title').data('repeater-default-title'));

        // Nested Repeaters always inherit the number of Rows from the previous Repeater, so this will fix that.
        var repeater        = $(this).closest('[data-edd-rbm-repeater]'),
            nestedRepeaters = $(this).find('.edd-rbm-nested-repeater');

        $(nestedRepeaters).each(function (index, nestedRepeater) {

            var items = $(nestedRepeater).find('.edd-rbm-repeater-item').get().reverse();

            if ( items.length == 1 ) return true; // Continue

            $(items).each(function (row, nestedRow) {

                if ( row == ( items.length - 1 ) ) return false; // Break

                $(nestedRow).stop().slideUp(300, function () {
                    $(this).remove();
                });

                $(repeater).trigger('edd-nested-repeater-cleanup', [$(nestedRow)]);

            });

        });

        init_edd_rbm_repeater_tooltips(this); // This is necessary to ensure any Rows that are added have Tooltips
        init_edd_rbm_repeater_required_fields(this); // Ensure that Required Fields get handled
        init_edd_rbm_repeater_options_button(this);

        initModals();

        $(this).stop().slideDown();

        var repeater = $(this).closest('[data-edd-rbm-repeater]');

        $(repeater).trigger('edd-rbm-repeater-add', [$(this)]);

    }

    var edd_fields_field_option_show = function () {

        init_edd_rbm_repeater_tooltips(this); // This is necessary to ensure any Rows that are added have Tooltips
        init_edd_rbm_repeater_required_fields(this); // Ensure that Required Fields get handled

        $(this).stop().slideDown();

        var repeater = $(this).closest('[data-edd-fields-field-options-repeater]');

        $(repeater).trigger('edd-fields-option-add', [$(this)]);

    }

    var edd_repeater_hide = function () {

        // For Nested Repeaters, just remove it. No Confirmation.
        if ( $(this).closest('.edd-rbm-repeater').hasClass('edd-rbm-nested-repeater') ) {

            $(this).slideUp(300, function () {
                $(this).remove();
            });

            return;
        }

        var repeater        = $(this).closest('[data-edd-rbm-repeater]'),
            confirmDeletion = confirm(eddFields.i18n.confirmDeletion);

        if ( confirmDeletion ) {

            var $row          = $(this),
                uuid          = $row.find('[data-repeater-edit]').data('open'),
                $modal        = $('[data-reveal="' + uuid + '"]'),
                templateIndex = get_edd_fields_index(uuid); // In this case we don't care if something is saved or not

            $row.addClass('deleting');
            $row.find('select, input, textarea, button').prop('disabled', true);
            $row.find('.title').append('<span class="spinner is-active" />');

            $.ajax({
                'type': 'POST',
                'url': eddFields.ajax,
                'data': {
                    'action': 'delete_edd_rbm_fields_template',
                    'index': templateIndex,
                    'saved': ( $row.data('saved') ) ? true : false,
                },
                success: function (response) {

                    // Remove whole DOM tree for the Modal.
                    $modal.parent().remove();

                    // Remove DOM Tree for the Notification "Header"
                    $row.stop();
                    setTimeout(function () {

                        $row.effect('highlight', {color: '#FFBABA'}, 300).dequeue().slideUp(300, function () {
                            $row.remove();
                            edd_repeater_reindex_primary();
                        });

                    });

                    $(repeater).trigger('edd-rbm-repeater-remove', [$row]);

                },
                error: function (request, status, error) {

                }
            });

        }

    }

    var edd_fields_field_option_hide = function () {

        $(this).slideUp(300, function () {
            $(this).remove();
        });

    }

    $repeaters.each(function () {

        var $repeater = $(this),
            $dummy    = $repeater.find('[data-repeater-dummy]');

        // Repeater
        $repeater.repeater({

            repeaters: [{
                selector: '.edd-rbm-nested-repeater',
                show: edd_repeater_show,
                hide: edd_repeater_hide,
                ready: function (setIndexes) {
                    $repeater.find('.edd-rbm-repeater-list').on('sortupdate', setIndexes);
                },
                repeaters: [{
                    selector: '.edd-fields-field-option-repeater',
                    show: edd_fields_field_option_show,
                    hide: edd_fields_field_option_hide,
                    ready: function (setIndexes) {
                        $repeater.find('.edd-rbm-repeater-list').on('sortupdate', setIndexes);
                    }
                }]
            }],
            show: edd_repeater_show,
            hide: edd_repeater_hide,
            ready: function (setIndexes) {
                // Custom Reindexing Function below
            }

        });

        if ( $dummy.length ) {
            $dummy.remove();
        }

        $repeater.find('.edd-rbm-repeater-list').sortable({
            axis: 'y',
            handle: '[data-repeater-item-handle]',
            forcePlaceholderSize: true,
            update: function (event, ui) {

                // If we're not in a Nested Repeater
                if ( !$(event.currentTarget).hasClass('edd-rbm-nested-repeater') ) {

                    edd_repeater_reindex_primary();

                    // Grab all data with their new indexes to save
                    var data = {};
					data.templates = [];
                    $('[data-edd-rbm-repeater] .edd-rbm-repeater-item').each(function (index, row) {

                        // Skip if we're not saved
                        if ( !$(row).data('saved') ) return true;

                        var uuid   = $(row).find('[data-repeater-edit]').data('open'),
                            $modal = $('[data-reveal="' + uuid + '"]');

                        data.templates.push(get_edd_fields_form($modal[0]));

                    });
					
					data.action = 'sort_edd_fields_templates';
					
					$.ajax({
						'type': 'POST',
						'url': eddFields.ajax,
						'data': data,
						success: function (response) {

							$repeater.trigger( 'edd-rbm-repeater-sorted' );

						},
						error: function (request, status, error) {

						}
					});

                } else {

                    init_edd_rbm_repeater_colorpickers($(event.currentTarget).closest('.edd-rbm-repeater-content'));
                    init_edd_rbm_repeater_tooltips($(event.currentTarget).closest('.edd-rbm-repeater-content'));
                    init_edd_rbm_repeater_required_fields($(event.currentTarget).closest('.edd-rbm-repeater-content'));
                    init_edd_rbm_repeater_options_button($(event.currentTarget).closest('.edd-rbm-repeater-content'));
                }

            }

        });

        $(document).on('closed.zf.reveal', '.edd-rbm-repeater-content.reveal', function () {

            var title = $(this).find('td:first-of-type input[type!="hidden"]'),
                uuid  = $(this).closest('.edd-rbm-repeater-content.reveal').data('reveal'),
                $row  = $('[data-open="' + uuid + '"]');

            if ( $(title).val() !== '' ) {
                $row.closest('.edd-rbm-repeater-item').find('.repeater-header div.title').html($(title).val());
            }
            else {
                var defaultValue = $row.closest('.edd-rbm-repeater-item').find('.repeater-header div.title').data('repeater-default-title');
                $row.closest('.edd-rbm-repeater-item').find('.repeater-header div.title').html(defaultValue);
            }

        });

        $(document).ready(function () {

            $(document).on('change', '.edd-fields-type', function (event) {
                init_edd_rbm_repeater_options_button($(event.currentTarget).closest('.edd-rbm-repeater-content'));
            });

        });

    });

})(jQuery);