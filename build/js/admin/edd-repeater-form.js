/**
 * [[Description]]
 * @param   {[[Type]]} modal [[Description]]
 * @returns {boolean}  [[Description]]
 */
function get_edd_fields_form( modal ) {
	
	// Used to construct HTML Name Attribute
	var repeaterList = jQuery( '.edd-fields-settings-repeater .edd-rbm-repeater-list' ).data( 'repeater-list' ),
		regex = new RegExp( repeaterList.replace( /[-\/\\^$*+?.()|[\]{}]/g, '\\$&' ) + '\\[\\d\\]\\[(.*)\\]', 'gi' ),
		data = {},
		uuid = jQuery( modal ).data( 'reveal' ),
		$row = jQuery( '[data-open="' + uuid + '"]' ).closest( '.edd-rbm-repeater-item' ),
		templateIndex = $row.index();

	var nestedRepeaterList = jQuery( '.edd-rbm-nested-repeater .edd-rbm-repeater-list' ).data( 'repeater-list' );

	// Holds all data for the Nested Repeater
	data[nestedRepeaterList] = [];

	jQuery( modal ).find( '[name]' ).each( function( index, field ) {

		if ( jQuery( field ).parent().hasClass( 'hidden' ) ) return true;

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

	data.index = templateIndex;
	
	return data;
	
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

				$form[0].reportValidity(); // Report Validity via HTML5 stuff

				if ( $form[0].checkValidity() ) { // Only run our code if we've got a Valid Form

					var data = get_edd_fields_form( modal );

					data.action = 'insert_edd_fields_template';

					$.ajax( {
						'type' : 'POST',
						'url' : eddFields.ajax,
						'data' : data,
						success : function( response ) {

							var uuid = $( modal ).data( 'reveal' ),
								$row = $( '[data-open="' + uuid + '"]' ).closest( '.edd-rbm-repeater-item' );

							closeModal( uuid );

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