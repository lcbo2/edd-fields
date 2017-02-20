if ( ! HTMLFormElement.prototype.reportValidity ) {

	/**
	 * Wait, people use IE and Safari outside of downloading Chrome?
	 * 
	 * @since	  1.0.0
	 * @return	  void
	 */
	HTMLFormElement.prototype.reportValidity = function () {

		var error = '',
			valid = true;

		// Remove all old Validation Errors
		jQuery( this ).find( '.validation-error' ).remove();

		jQuery( this ).find( '.required' ).each( function( index, element ) {

			// Reset Custom Validity Message
			element.setCustomValidity( '' );

			if ( ! jQuery( element ).closest( 'td' ).hasClass( 'hidden') && 
				( jQuery( element ).val() === null || jQuery( element ).val() == '' ) ) {
				
				error = jQuery( element ).data( 'validity-error' );

				element.setCustomValidity( error );
				jQuery( element ).before( '<span class="validation-error">' + error + '</span>' );

				valid = false;

			}

		} );
		
		var $templateName = jQuery( this ).find( '.edd-fields-template-name' ),
			templateName = ( $templateName.val() == '' ) ? $templateName.attr( 'placeholder' ) : $templateName.val(),
			uuid = jQuery( this ).closest( '.reveal' ).data( 'reveal' );

		$templateName[0].setCustomValidity( '' ); // Reset Validity Message
		
		jQuery( '.edd-rbm-repeater-item' ).each( function( index, row ) {
			
			// If this is the current Item, ignore
			if ( jQuery( row ).find( '[data-repeater-edit]' ).data( 'open' ) == uuid ) return true;
					
			if ( edd_fields_sanitize_key( jQuery( row ).find( '.title' ).text().trim() ) == edd_fields_sanitize_key( templateName.trim() ) ) {

				$templateName[0].setCustomValidity( $templateName.data( 'validity-error' ) );
				
				$templateName.before( '<span class="validation-error">' + error + '</span>' );
				
				valid = false;

				return false; // break loop

			}

		} );

		if ( ! valid ) {

			jQuery( this ).closest( '.reveal-overlay' ).scrollTop( jQuery( this ).find( '.validation-error:first-of-type' ) );
			return valid;

		}

		return valid;

	};

};