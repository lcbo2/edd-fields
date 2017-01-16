( function( $ ) {
	
	$( document ).ready( function() {
		
		if ( $( '.edd-fields-tabs' ).length > 0 ) {
			
			$( '.edd-fields-meta-box .hidden' ).removeClass( 'hidden' );
			$( '.edd-fields-meta-box' ).tabs( {
				active: 'custom',
			} );
			
		}
		
	} );
	
} )( jQuery );