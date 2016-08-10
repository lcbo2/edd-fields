// Repeaters
( function ( $ ) {

    var $repeaters = $( '[data-edd-repeater]' );
    console.log( $repeaters );

    if ( ! $repeaters.length ) {
        return;
    }

    $repeaters.each( function () {

        var $repeater = $( this );

        // Repeater
        $repeater.repeater( {
            
            repeaters: [ {
                // (Required)
                // Specify the jQuery selector for this nested repeater
                selector: '.nested-repeater'
            } ],
            hide: function () {

                $( this ).stop().slideUp( 300, function () {
                    $(this).remove();
                } );

                $repeater.trigger( 'edd-repeater-remove', [$( this )] );
                
            },
            show: function () {
                
                var index = $( this ).index();

                $( this ).find( '[data-repeater-item-handle]' ).text( index + 1 );

                // Hide current title for new item and show default title
                $( this ).find( '[data-repeater-collapsable-handle-title]' ).hide();
                $( this ).find( '[data-repeater-collapsable-handle-default]' ).show();

                $( this ).addClass( 'opened' ).removeClass( 'closed' ).stop().slideDown();

                //init_colorpickers();
                //init_datepickers();

                $repeater.trigger( 'edd-repeater-add', [$( this )] );
                
            },
            ready: function ( setIndexes ) {
                $repeater.find( 'tbody' ).on( 'sortupdate', setIndexes );
            }
            
        } );

        /*
        // Dummy item
        if ( $dummy.length ) {
            $dummy.remove();
        }
        */

        // Sortable
        if ( typeof $repeater.attr( 'data-repeater-sortable' ) !== 'undefined' ) {
            $repeater.find( '.edd-repeater-list' ).sortable( {
                axis: 'y',
                handle: '[data-repeater-item-handle]',
                forcePlaceholderSize: true,
                update: function (e, ui) {

                    // Update the number in each row
                    $repeater.find( '.edd-repeater-item' ).each( function () {
                        var index = $( this ).index();
                        $( this ).find( '[data-repeater-item-handle]' ).text( index + 1 );
                    } );

                    //init_colorpickers();
                    //init_datepickers();
                    
                }
                
            } );
            
        }

        // Collapsable
        if ( typeof $repeater.attr( 'data-repeater-collapsable' ) !== 'undefined' ) {
            $repeater.find( '.edd-repeater-item-content' ).hide();
        }

        $( document ).on( 'click touchend', '.edd-repeater[data-repeater-collapsable] [data-repeater-collapsable-handle]', function () {

            var $repeater_field = $( this ).closest( '.edd-repeater-item' ),
                $content = $repeater_field.find( '.edd-repeater-item-content' ),
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
        
        $repeater.on( 'edd-repeater-add', function() {
            console.log( 'clicked' );
        } );
        
    } );
    
} )( jQuery );