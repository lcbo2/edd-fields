( function ( $ ) {

    var EDD_Download_Configuration = {
        init : function() {
            this.add();
            this.move();
            this.remove();
            this.type();
            this.prices();
            this.files();
            this.updatePrices();
        },
        clone_repeatable : function(row) {

            // Retrieve the highest current key
            var key = highest = 1;
            row.parent().find( 'tr.edd_repeatable_row' ).each(function() {
                var current = $(this).data( 'key' );
                if( parseInt( current ) > highest ) {
                    highest = current;
                }
            });
            key = highest += 1;

            clone = row.clone();

            /** manually update any select box values */
            clone.find( 'select' ).each(function() {
                $( this ).val( row.find( 'select[name="' + $( this ).attr( 'name' ) + '"]' ).val() );
            });

            clone.removeClass( 'edd_add_blank' );

            clone.attr( 'data-key', key );
            clone.find( 'td input, td select, textarea' ).val( '' );
            clone.find( 'input, select, textarea' ).each(function() {
                var name = $( this ).attr( 'name' );
                var id   = $( this ).attr( 'id' );

                if( name ) {

                    name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']');
                    $( this ).attr( 'name', name );

                }

                if( typeof id != 'undefined' ) {

                    id = id.replace( /(\d+)/, parseInt( key ) );
                    $( this ).attr( 'id', id );

                }

            });

            clone.find( 'span.edd_price_id' ).each(function() {
                $( this ).text( parseInt( key ) );
            });

            clone.find( 'span.edd_file_id' ).each(function() {
                $( this ).text( parseInt( key ) );
            });

            clone.find( '.edd_repeatable_default_input' ).each( function() {
                $( this ).val( parseInt( key ) ).removeAttr('checked');
            })

            // Remove Chosen elements
            clone.find( '.search-choice' ).remove();
            clone.find( '.chosen-container' ).remove();

            return clone;
        },

        add : function() {
            $( document.body ).on( 'click', '.submit .edd_add_repeatable', function(e) {
                e.preventDefault();
                var button = $( this ),
                    row = button.parent().parent().prev( 'tr' ),
                    clone = EDD_Download_Configuration.clone_repeatable(row);

                clone.insertAfter( row ).find('input, textarea, select').filter(':visible').eq(0).focus();

                // Setup chosen fields again if they exist
                clone.find('.edd-select-chosen').chosen({
                    inherit_select_classes: true,
                    placeholder_text_single: edd_vars.one_option,
                    placeholder_text_multiple: edd_vars.one_or_more_option,
                });
                clone.find( '.edd-select-chosen' ).css( 'width', '100%' );
                clone.find( '.edd-select-chosen .chosen-search input' ).attr( 'placeholder', edd_vars.search_placeholder );
            });
        },

        move : function() {

            $(".edd_repeatable_table tbody").sortable({
                handle: '.edd_draghandle', items: '.edd_repeatable_row', opacity: 0.6, cursor: 'move', axis: 'y', update: function() {
                    var count  = 0;
                    $(this).find( 'tr' ).each(function() {
                        $(this).find( 'input.edd_repeatable_index' ).each(function() {
                            $( this ).val( count );
                        });
                        count++;
                    });
                }
            });

        },

        remove : function() {
            $( document.body ).on( 'click', '.edd_remove_repeatable', function(e) {
                e.preventDefault();

                var row   = $(this).parent().parent( 'tr' ),
                    count = row.parent().find( 'tr' ).length - 1,
                    type  = $(this).data('type'),
                    repeatable = 'tr.edd_repeatable_' + type + 's',
                    focusElement,
                    focusable,
                    firstFocusable;

                // Set focus on next element if removing the first row. Otherwise set focus on previous element.
                if ( $(this).is( '.ui-sortable tr:first-child .edd_remove_repeatable:first-child' ) ) {
                    focusElement  = row.next( 'tr' );
                } else {
                    focusElement  = row.prev( 'tr' );
                }

                focusable  = focusElement.find( 'select, input, textarea, button' ).filter( ':visible' );
                firstFocusable = focusable.eq(0);

                if ( type === 'price' ) {
                    var price_row_id = row.data('key');
                    /** remove from price condition */
                    $( '.edd_repeatable_condition_field option[value="' + price_row_id + '"]' ).remove();
                }

                if( count > 1 ) {
                    $( 'input, select', row ).val( '' );
                    row.fadeOut( 'fast' ).remove();
                    firstFocusable.focus();
                } else {
                    switch( type ) {
                        case 'price' :
                            alert( edd_vars.one_price_min );
                            break;
                        case 'file' :
                            $( 'input, select', row ).val( '' );
                            break;
                        default:
                            alert( edd_vars.one_field_min );
                            break;
                    }
                }

                /* re-index after deleting */
                $(repeatable).each( function( rowIndex ) {
                    $(this).find( 'input, select' ).each(function() {
                        var name = $( this ).attr( 'name' );
                        name = name.replace( /\[(\d+)\]/, '[' + rowIndex+ ']');
                        $( this ).attr( 'name', name ).attr( 'id', name );
                    });
                });
            });
        },

    };

    EDD_Download_Configuration.init();

} );