( function( $ ) {
	
	$( document ).ready( function() {
		
		if ( $( '.edd-fields-tabs' ).length > 0 ) {
			
			var $active = $( 'input[name="edd_fields_tab"]' );
			
			$( '.edd-fields-meta-box .hidden' ).removeClass( 'hidden' );
			$( '.edd-fields-meta-box' ).tabs( {
				active: $active.data( 'index' ),
				activate: function( event, tabs ) {
					
					var index = tabs.newTab.index(),
						value = tabs.newPanel[0].id;
					
					$active.attr( 'data-index', index );
					$active.val( value );
				},
			} );
			
		}
		
	} );
	
} )( jQuery );