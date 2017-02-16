( function( $ ) {
	
	$( document ).on( 'ready', function () {
	
		if ( $( '#edd_fields_meta_box select[name="edd_fields_template"]' ).length > 0 ) {

			$( document ).on( 'change', '#edd_fields_meta_box select[name="edd_fields_template"]', function() {

				$( '#edd_fields_meta_box .edd-fields-template' ).addClass( 'hidden' );

				$( '#edd_fields_meta_box #edd-fields-' + $( this ).val() ).removeClass( 'hidden' );

			} );

		}
		
	} );
	
} )( jQuery );