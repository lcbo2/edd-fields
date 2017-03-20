/**
 * Gets the Index of a Row excluding all unsaved Rows
 * 
 * @param		{string}  uuid Unique Identifier of a Row/Modal relationship
 *                        
 * @since		1.0.0
 * @returns 	{integer} Index
 */
function get_edd_fields_index( uuid ) {
	
	var templateIndex = 0;
	jQuery( '.edd-rbm-repeater-item' ).each( function( index, row ) {
		
		if ( jQuery( row ).find( '[data-repeater-edit]' ).data( 'open' ) == uuid ) {
			return false; // End loop, we found it
		}
		
		if ( ! jQuery( row ).data( 'saved' ) ) {
			return true; // Next iteration, this doesn't match our modal and it isn't saved
		}
		
		templateIndex++;
		
	} );
	
	return templateIndex;
	
}

/**
 * Dynamically Grabs all Data in the Form
 * 
 * @param 		{object} modal Modal JavaScript Object
 *                         
 * @since		1.0.0
 * @returns 	{object} Key=>Value of each Field in the Form
 */
function get_edd_fields_form( modal ) {
	
	// Used to construct HTML Name Attribute
	var repeaterList = jQuery( '.edd-fields-settings-repeater .edd-rbm-repeater-list' ).data( 'repeater-list' ),
		data = {},
		uuid = jQuery( modal ).data( 'reveal' ),
		$row = jQuery( '[data-open="' + uuid + '"]' ).closest( '.edd-rbm-repeater-item' ),
		templateIndex = get_edd_fields_index( uuid );

	var nestedRepeaterList = jQuery( '.edd-rbm-nested-repeater > table > .edd-rbm-repeater-list' ).data( 'repeater-list' );

	// Holds all data for the Nested Repeater
	data[nestedRepeaterList] = [];

	jQuery( modal ).find( '.edd-fields-field' ).each( function( index, field ) {

		if ( jQuery( field ).parent().hasClass( 'hidden' ) ) return true;
		
		var name = jQuery( field ).attr( 'name' ),
			value = jQuery( field ).val(),
			isRepeater = false;
		
		if ( jQuery( field ).is( 'tr' ) ) {
			
			value = {},
				isRepeater = true;
			
			jQuery( field ).find( '[name]' ).each( function( fieldIndex, repeaterField ) {
				
				name = jQuery( repeaterField ).attr( 'name' ),
					name = name.replace( /.*]\[(.*)]$/, '$1' ),
					value[ name ] = jQuery( repeaterField ).val();
				
			} );
			
		}
		else {
			
			// In this case, it is not undefined
			name = name.replace( /.*]\[(.*)]$/, '$1' );
			
		}
		
		if ( jQuery( field ).is( 'input[type="checkbox"]' ) ) {

			value = ( jQuery( field ).prop( 'checked' ) ) ? 1 : 0;

		}

		// Not part of the nested Repeater (Field Creation, not Options)
		// These are pretty basic fields, just assign the value
		if ( ! isRepeater ) {
			data[ name ] = value;
		}
		else {

			// We already have our "Value" as an Object to push
			data.edd_fields_template_fields.push( value );
			
		}

	} );
	
	/**
	
	jQuery( modal ).find( '[data-options-repeater-edit]' ).each( function( index, options ) {
		
		var uuid = jQuery( options ).data( 'open' ),
			$optionsModal = jQuery( '[data-reveal="' + uuid + '"]' );
		
		$optionsModal.find( '[name]' ).each( function( optionIndex, field ) {
			
			var name = jQuery( field ).attr( 'name' ),
				match = regex.exec( name ),
				value = jQuery( field ).val();
			
			if ( jQuery( field ).is( 'input[type="checkbox"]' ) ) {

				value = ( jQuery( field ).prop( 'checked' ) ) ? 1 : 0;

			}

			if ( name.indexOf( nestedRepeaterList ) == -1 ) {
				// Checkboxes don't play nice with my regex and I'm not rewriting it
				data[ match[1].replace( '][', '' ) ] = value;
			}
			else {

				// This is the name of the individual field
				var nestedFieldKey = name.replace( /.*\[/, '' ).replace( ']', '' );

				data.edd_fields_template_fields.push( {
					[nestedFieldKey]: value,
				} );
			}

			// Reset Interal Pointer for Regex
			regex.lastIndex = 0;
			
		} );
		
	} );
	
	*/

	data.index = templateIndex;
	
	return data;
	
}

/**
 * Sanitizes a String similarly to how we do in PHP-Land
 * 
 * @param		{string} key String Key
 *                      
 * @since		1.0.0
 * @returns 	{string} Sanitized String Key
 */
function edd_fields_sanitize_key( key ) {
	
	return key.replace( /[\W|_]/g, '', ).toLowerCase();
	
}

( function( $ ) {

	/**
	 * Submit the Form for Creating/Updating Notifications via their Modals
	 * 
	 * @param	  	{object}  event JavaScript Event Object
	 *							  
	 * @since	  	1.0.0
	 * @returns	 	{boolean} Validity of Form
	 */
	var attachNotificationSubmitEvent = function( event ) {

		var modal = event.currentTarget,
			$form = $( modal ).find( 'form' );

		if ( ! $( modal ).hasClass( 'has-form' ) ) {

			// We need to create the Form
			// "novalidate" so that HTML5 doesn't try to take over before we can do our thing
			$form = $( modal ).find( '.edd-rbm-repeater-form' ).wrap( '<form method="POST" novalidate></form>' ).parent();

			$( modal ).addClass( 'has-form' );

			// Normally HTML doesn't like us having nested Forms, so we force it like this
			// By the time the Modal opens and this code runs, the Form isn't nested anymore
			$form.submit( function( event ) {

				event.preventDefault(); // Don't submit the form via PHP
				
				var $templateName = $form.find( '.edd-fields-template-name' ),
					templateName = ( $templateName.val() == '' ) ? $templateName.attr( 'placeholder' ) : $templateName.val(),
					uuid = $( modal ).data( 'reveal' );
				
				$templateName[0].setCustomValidity( '' ); // Reset Validity Message
				
				$( '.edd-rbm-repeater-item' ).each( function( index, row ) {
					
					// If this is the current Item, ignore
					if ( $( row ).find( '[data-repeater-edit]' ).data( 'open' ) == uuid ) return true;
					
					if ( edd_fields_sanitize_key( $( row ).find( '.title' ).text().trim() ) == edd_fields_sanitize_key( templateName.trim() ) ) {
						
						$templateName[0].setCustomValidity( $templateName.data( 'validity-error' ) );
						
						return false; // break loop
						
					}
					
				} );

				$form[0].reportValidity(); // Report Validity via HTML5 stuff

				if ( $form[0].checkValidity() ) { // Only run our code if we've got a Valid Form

					var data = get_edd_fields_form( modal ),
						uuid = $( modal ).data( 'reveal' ),
						$row = $( '[data-open="' + uuid + '"]' ).closest( '.edd-rbm-repeater-item' );;

					data.action = 'insert_edd_fields_template';
					data.saved = ( $row.data( 'saved' ) ) ? true : false;
					
					console.log( data );
					return false;

					$.ajax( {
						'type' : 'POST',
						'url' : eddFields.ajax,
						'data' : data,
						success : function( response ) {

							closeModal( uuid );
							
							// Update our Item to show it is saved
							$row.attr( 'data-saved', true );

							// Highlight Green
							$row.effect( 'highlight', { color : '#DFF2BF' }, 1000 );

						},
						error : function( request, status, error ) {
						}
					} );

				}

			} );

		}

	}

	$( document ).ready( function() {

		// When a Modal opens, attach the Form Submission Event
		$( document ).on( 'open.zf.reveal', '.edd-rbm-repeater-content.reveal', function( event ) {
			attachNotificationSubmitEvent( event );
		} );

	} );

} )( jQuery );