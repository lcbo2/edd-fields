( function( $ ) {
	
	var getFieldsForDownload = function( postID = 0, $form ) {
		
		$.ajax( {
			'type' : 'POST',
			'url' : ajaxurl,
			'data' : {
				'action' : 'get_edd_fields_widget_field',
				'post_id' : postID,
			},
			success : function( response ) {
				
				var $select = $form.find( '.edd-fields-widget-field' ),
					selected = $select.data( 'selected' ),
					$fields = $form.find( '.edd-fields-widget-field' ),
					$prefix = $form.find( '.edd-fields-widget-prefix' );
				
				$select.empty();
				
				for ( var value in response.data ) {
					
					$select.append( '<option value="' + value + '"' + ( ( value == selected ) ? ' selected' : '' ) + '>' + response.data[value] + '</option>' );
					
				}
				
				var field = $fields.find( 'option:selected' ).text();
				
				$prefix.attr( 'placeholder', field + ': ' );

			},
			error : function( request, status, error ) {

			}
		} );
		
	}
	
	$( document ).on( 'ready', function() {
		
		if ( $( '.edd-fields-widget-form' ).length > 0 ) {
			
			// When the Shortcode type changes
			$( document ).on( 'change', '.edd-fields-widget-form .edd-fields-widget-shortcode', function() {
				
				if ( $( this ).val() == 'individual' ) {
					
					var $form = $( this ).closest( '.edd-fields-widget-form' ),
						postID = $form.find( '.edd-fields-widget-post-id' ).val();
					
					getFieldsForDownload( postID, $form );
					
					$form.find( '.edd-fields-individual-options' ).removeClass( 'hidden' );
					
				}
				else {
					$( this ).closest( '.edd-fields-widget-form' ).find( '.edd-fields-individual-options' ).addClass( 'hidden' );
				}
				
			} );
			
			// When the Selected Post changes
			$( document ).on( 'change', '.edd-fields-widget-form .edd-fields-widget-post-id', function() {
				
				var $form = $( this ).closest( '.edd-fields-widget-form' ),
					postID = $( this ).val(),
					shortcodeToggle = $form.find( '.edd-fields-widget-shortcode:checked' ).val();
				
				// We only need to do AJAX if we're set to Individual, as changing to Individual will trigger it anyway
				if ( shortcodeToggle == 'individual' ) {
					
					getFieldsForDownload( postID, $form );
					
				}
				
			} );
			
			// When the Selected Field Changes
			$( document ).on( 'change', '.edd-fields-widget-form .edd-fields-widget-field', function() {
				
				var $form = $( this ).closest( '.edd-fields-widget-form' ),
					field = $( this ).find( 'option:selected' ).text(),
					$prefix = $form.find( '.edd-fields-widget-prefix' );
				
				$prefix.attr( 'placeholder', field + ': ' );
				
			} );
			
			// When we open the Widget Settings
			$( document ).on( 'click touched', '.widget-top', function() {

				// If we've already loaded up necessary data or if the Widget doesn't match anyway, bail
				if ( $( this ).closest( '.widget' ).attr( 'id' ).indexOf( 'edd_fields') == -1 ||
				   $( this ).data( 'edd-fields-widget' ) == 'loaded' ) {
					return;
				}

				var $form = $( this ).siblings( '.widget-inside' ).find( '.edd-fields-widget-form' ),
					postID = $form.find( '.edd-fields-widget-post-id' ).val();

				getFieldsForDownload( postID, $form );

				// Set the loaded flag
				$( this ).attr( 'data-edd-fields-widget', 'loaded' );

			} );
			
		}
		
	} );
	
} )( jQuery );