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

                // Hide current title for new item and show default title
                $( this ).find( '[data-repeater-collapsable-handle-title]' ).hide();
                $( this ).find( '[data-repeater-collapsable-handle-default]' ).show();

                $( this ).addClass( 'opened' ).removeClass( 'closed' ).stop().slideDown();

                $repeater.trigger( 'edd-repeater-add', [$( this )] );
                
            },
            ready: function ( setIndexes ) {
                $repeater.find( 'tbody' ).on( 'sortupdate', setIndexes );
            }
            
        } );

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
            
            console.log( $content );

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