/**
 * Normally something like this would be handled by $( document ).foundation(), but doing it manually lets us call this whenever we'd like and dynamically create Button->Modal associations
 * 
 * @since	  1.0.0
 * @return	  void
 */
function initModals() {

	// Primary Modal Repeater
	jQuery( '.edd-rbm-repeater .edd-rbm-repeater-item' ).each( function( index, item ) {

		var $modal = jQuery( item ).find( '.edd-rbm-repeater-content.reveal' );

		if ( $modal.attr( 'data-reveal' ) !== '' ) return true;

		// Copy of how Foundation creates UUIDs
		var uuid = Math.round( Math.pow( 36, 7 ) - Math.random() * Math.pow( 36, 6 ) ).toString( 36 ).slice( 1 ) + '-reveal';

		$modal.attr( 'data-reveal', uuid );

		var $editButton = jQuery( item ).find( 'input[data-repeater-edit]' ).attr( 'data-open', uuid );

		$modal = new Foundation.Reveal( $modal );

	} );
	
	// Modal Repeater for Field Options
	jQuery( '.edd-rbm-nested-repeater .edd-rbm-repeater-item' ).each( function( index, item ) {

		var $modal = jQuery( item ).find( '.edd-fields-field-options.reveal' );

		if ( $modal.attr( 'data-reveal' ) !== '' ) return true;

		// Copy of how Foundation creates UUIDs
		var uuid = Math.round( Math.pow( 36, 7 ) - Math.random() * Math.pow( 36, 6 ) ).toString( 36 ).slice( 1 ) + '-reveal';

		$modal.attr( 'data-reveal', uuid );

		var $editButton = jQuery( item ).find( 'input[data-options-repeater-edit]' ).attr( 'data-open', uuid );

		$modal = new Foundation.Reveal( $modal );

	} );

}

/**
 * Opens a Modal because Foundation isn't able to do things quite how I need
 * 
 * @param	  {Event|String} uuid Either the Event from creating a new Row or a UUID
 * @param	  {object}	  row  DOM Object of the Row if called from an Event
 *							
 * @since	  1.0.0
 * @return	  void
 */
function openModal( uuid, row ) {

	// Handle newly created Rows
	if ( uuid.type == 'edd-rbm-repeater-add' ) {

		var $row = jQuery( row );

		uuid = $row.find( 'input[data-repeater-edit]' ).data( 'open' );

	}

	var $modal = jQuery( '[data-reveal="' + uuid + '"]' );

	$modal.foundation( 'open' );

	// Ensure we're looking at the top of the Modal
	$modal.closest( '.reveal-overlay' ).scrollTop( 0 );

}

/**
 * Closes a Modal by its UUID
 * 
 * @param	  {string} uuid UUID of the Modal
 *					  
 * @since	  1.0.0
 * @return	  void
 */
function closeModal( uuid ) {

	var $modal = jQuery( '[data-reveal="' + uuid + '"]' );

	$modal.foundation( 'close' );

}

( function( $ ) {
	'use strict';

	$( document ).ready( function() {

		initModals();
		
		// On Page load, assume all are saved (As they are)
		$( 'div[data-repeater-list="edd_fields_template_settings"] > .edd-rbm-repeater-item' ).each( function( index, row ) {
			$( row ).attr( 'data-saved', true );
		} );

		// This JavaScript only loads on our custom Page, so we're fine doing this
		var $repeaters = $( '[data-edd-rbm-repeater]' );

		if ( $repeaters.length ) {
			$repeaters.on( 'edd-rbm-repeater-add', initModals );
			$repeaters.on( 'edd-rbm-repeater-add', openModal );
		}

	} );

	$( document ).on( 'click touched', '[data-repeater-edit], [data-options-repeater-edit]', function() {

		openModal( $( this ).data( 'open' ) );

	} );

	$( document ).on( 'open.zf.reveal', '.edd-rbm-repeater-content.reveal', function() {

		init_edd_rbm_repeater_colorpickers( this );
		init_edd_rbm_repeater_tooltips( this );
		init_edd_rbm_repeater_required_fields( this );
		init_edd_rbm_repeater_options_button( this );
		
		$( this ).find( '.edd-fields-template-name' ).attr( 'data-validity-error', eddFields.i18n.duplicateNameError );

	} );

} )( jQuery );