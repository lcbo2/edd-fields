( function( $ ) {
	
	var getFieldsForDownload = function( postID = 0, $form ) {
		
		
		
	}
	
	$( document ).on( 'ready', function() {
		
		if ( $( '.edd-fields-widget-form' ).length > 0 ) {
			
			$( document ).on( 'change', '.edd-fields-widget-form .edd-fields-widget-shortcode', function() {
				
				if ( $( this ).val() == 'individual' ) {
					
					var $form = $( this ).closest( '.edd-fields-widget-form' );
						//postID = $form.find( '';
					
					//getFieldsForDownload( postID, $( this ).closest( '.edd-fields-widget-form' ) );
					
					$form.find( '.edd-fields-individual-options' ).removeClass( 'hidden' );
					
				}
				else {
					$( this ).closest( '.edd-fields-widget-form' ).find( '.edd-fields-individual-options' ).addClass( 'hidden' );
				}
				
			} );
			
		}
		
	} );
	
} )( jQuery );