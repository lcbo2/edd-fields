// Repeaters
( function ( $ ) {

    var $repeaters = $( '[data-edd-repeater]' );

    if ( ! $repeaters.length ) {
        return;
    }

    var edd_repeater_show = function() {

        // Hide current title for new item and show default title
        $( this ).find( '.repeater-header h2 span.title' ).html( $( this ).find( '.repeater-header h2' ).data( 'repeater-collapsable-default' ) );

        // Nested Repeaters always inherit the number of Rows from the previous Repeater, so this will fix that.
        var repeater = $( this ).closest( '[data-edd-repeater]' ),
            nestedRepeaters = $( this ).find( '.nested-repeater' );

        $( nestedRepeaters ).each( function( index, nestedRepeater ) {

            var items = $( nestedRepeater ).find( '.edd-repeater-item' ).get().reverse();

            if ( items.length == 1 ) return true; // Continue

            $( items ).each( function( row, nestedRow ) {

                if ( row == ( items.length - 1 ) ) return false; // Break

                $( nestedRow ).stop().slideUp( 300, function() {
                    $( this ).remove();
                } );

                $( repeater ).trigger( 'edd-nested-repeater-cleanup', [$( nestedRow )] );

            } );

        } );

        $( this ).addClass( 'opened' ).removeClass( 'closed' ).stop().slideDown();

        $( repeater ).trigger( 'edd-repeater-add', [$( this )] );

    }

    var edd_repeater_hide = function() {

        var repeater = $( this ).closest( '[data-edd-repeater]' );

        $( this ).stop().slideUp( 300, function () {
            $(this).remove();
        } );

        $( repeater ).trigger( 'edd-repeater-remove', [$( this )] );

    }

    $repeaters.each( function () {

        var $repeater = $( this ),
            $dummy = $repeater.find( '[data-repeater-dummy]' );

        // Repeater
        $repeater.repeater( {

            repeaters: [ {
                // (Required)
                // Specify the jQuery selector for this nested repeater
                selector: '.nested-repeater',
                show: edd_repeater_show,
                hide: edd_repeater_hide,
            } ],
            show: edd_repeater_show,
            hide: edd_repeater_hide,
            ready: function ( setIndexes ) {
                $repeater.find( 'tbody' ).on( 'sortupdate', setIndexes );
            }

        } );

        if ( $dummy.length ) {
            $dummy.remove();
        }

        // Sortable
        if ( typeof $repeater.attr( 'data-repeater-sortable' ) !== 'undefined' ) {
            $repeater.find( '.edd-repeater-list' ).sortable( {
                axis: 'y',
                handle: '[data-repeater-item-handle]',
                forcePlaceholderSize: true,
                update: function ( e, ui ) {
                }

            } );

        }

        // Collapsable
        if ( typeof $repeater.attr( 'data-repeater-collapsable' ) !== 'undefined' ) {
            $repeater.find( '.edd-repeater-content' ).first().hide();
        }

        $( document ).on( 'click touchend', '.edd-repeater[data-repeater-collapsable] [data-repeater-collapsable-handle]', function () {

            var $repeater_field = $( this ).closest( '.edd-repeater-item' ),
                $content = $repeater_field.find( '.edd-repeater-content' ).first(),
                status = $repeater_field.hasClass( 'opened' ) ? 'closing' : 'opening';

            if ( status == 'opening' ) {

                $content.stop().slideDown();
                $repeater_field.addClass( 'opened' );
                $repeater_field.removeClass( 'closed' );

            }
            else {

                $content.stop().slideUp();
                $repeater_field.addClass( 'closed' );
                $repeater_field.removeClass( 'opened' );
            }

        } );

        $( document ).on( 'keyup change', '.edd-repeater .edd-repeater-content td:first-of-type *[name^="edd_settings"]', function() {
            $( this ).closest( '.edd-repeater-item' ).find( '.repeater-header h2 span.title' ).html( $( this ).val() );
        } );

    } );

} )( jQuery );