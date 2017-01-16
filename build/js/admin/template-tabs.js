( function( $ ) {
	
	$( document ).ready( function() {
		
		if ( $( '.edd-fields-tabs' ).length > 0 ) {
			
			$( '.edd-fields-meta-box .hidden' ).removeClass( 'hidden' );
			$( '.edd-fields-meta-box' ).tabs( {
				active: $( 'input[name="edd_fields_tab"]' ).val(),
				activate: function( event, tabs ) {
					$( 'input[name="edd_fields_tab"]' ).val( tabs.newTab.index() );
				},
			} );
			
		}
		
	} );
	
} )( jQuery );