( function( $ ) {
	
	$( document ).ready( function() {
		
		if ( $( '.edd-fields-tabs' ).length > 0 ) {
			
			$( '.edd-fields-meta-box .hidden' ).removeClass( 'hidden' );
			$( '.edd-fields-meta-box' ).tabs( {
				active: $( 'input[name="edd_fields_tab"]' ).val(),
				activate: function( event, tabs ) {
					
					var value = tabs.newTab.index();
					
					// Time to combat PHP falsiness
					// '0' !== false, dangit
					if ( value == '0' ) value = '0x0';
					
					$( 'input[name="edd_fields_tab"]' ).val( value );
				},
			} );
			
		}
		
	} );
	
} )( jQuery );