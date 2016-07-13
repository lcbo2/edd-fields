( function( $ ) {

    var EDD_Fields_Repeater = {

        init: function() {
            this.fields();
        },

        fields: function() {

            $( '#edd-fields-repeater tbody' ).sortable( {
                handle: '.handle',
                update: function() {
                    EDD_Fields_Repeater.reIndex(); 
                }
            } );

            // Insert EDD Fields row
            $( '#edd-fields-add-row' ).on( 'click', function() {
                var row = $( '#edd-fields-repeater tr:last' );
                var clone = row.clone();
                var count = row.parent().find( 'tr' ).length;
                clone.find( 'td input' ).not( ':input[type=checkbox]' ).val( '' );
                clone.find( 'td [type="checkbox"]' ).attr( 'checked', false);
                clone.find( 'input, select' ).each( function() {
                    var name = $( this ).attr( 'name' );
                    name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']' );
                    $( this ).attr( 'name', name ).attr( 'id', name );
                } );
                clone.find( 'label' ).each( function() {
                    var name = $( this ).attr( 'for' );
                    name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']' );
                    $( this ).attr( 'for', name );
                } );
                clone.insertAfter( row );
                return false;
            } );

            // Remove EDD Fields row
            $( document.body ).on( 'click', '#edd-fields-repeater .edd-remove-row', function() {

                var rows = $( '#edd-fields-repeater tr:visible' );
                var count     = rows.length;

                if ( count === 2 ) {
                    $( '#edd-fields-repeater select' ).val( '' );
                    $( '#edd-fields-repeater input[type="text"]' ).val( '' );
                    $( '#edd-fields-repeater input[type="number"]' ).val( '' );
                    $( '#edd-fields-repeater input[type="checkbox"]' ).attr( 'checked', false);
                }
                else {
                    $( this ).closest( 'tr' ).remove();
                }

                /* re-index after deleting */
                EDD_Fields_Repeater.reIndex();

                return false;
            } );

        },

        reIndex: function() {

            $( '#edd-fields-repeater tr' ).each( function( rowIndex ) {
                $( this ).children().find( 'input, select' ).each( function() {
                    var name = $( this ).attr( 'name' );
                    name = name.replace( /\[(\d+)\]/, '[' + ( rowIndex - 1 ) + ']' );
                    $( this ).attr( 'name', name ).attr( 'id', name );
                } );
            } );

        }

    };

    EDD_Fields_Repeater.init();

} )( jQuery );