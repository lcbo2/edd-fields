// Initialize special fields if they exist
function init_edd_repeater_colorpickers( modal ) {

	var regex = /value="(#(?:[0-9a-f]{3}){1,2})"/i;

	// Only try to run if there are any Color Pickers within an EDD Repeater
	if ( jQuery( modal ).find( '.edd-color-picker' ).length ) {

		// Hit each colorpicker individually to ensure its settings are properly used
		jQuery( modal ).find( '.edd-color-picker' ).each( function( index, colorPicker ) {

			// Value exists in HTML but is inaccessable via JavaScript. No idea why.
			var value = regex.exec( jQuery( colorPicker )[0].outerHTML )[1];

			jQuery( colorPicker ).val( value ).attr( 'value', value ).wpColorPicker();

		} );

	}

}

function eddFieldsSelect2Icons( icon, container = null ) {
		
	return icon.text + '<span class="icon ' + icon.id + '"></span>';

}

function init_edd_repeater_select2( modal ) {

	// Only try to run if there are any Chosen Fields within an EDD Repeater
	if ( jQuery( modal ).find( '.edd-chosen' ).length ) {

		// Just kidding, Select2 is better!
		jQuery( modal ).find( '.edd-chosen' ).select2( {
			width: '100%',
			templateResult: eddFieldsSelect2Icons,
			templateSelection: eddFieldsSelect2Icons,
			containerCssClass: 'edd-fields-select2 select2-container',
			dropdownCssClass: 'edd-fields-select2 select2-container',
			escapeMarkup: function(m) {	
				return m;
			}
		} );

	}

}

function init_edd_repeater_tooltips( modal ) {
	
	jQuery( modal ).find( '.edd-help-tip' ).each( function( index, tooltip ) {
			
		jQuery( tooltip ).tooltip( {
			content: function() {
				return jQuery( tooltip ).prop( 'title' );
			},
			tooltipClass: 'edd-ui-tooltip',
			position: {
				my: 'center top',
				at: 'center bottom+10',
				collision: 'flipfit',
			},
			hide: {
				duration: 200,
			},
			show: {
				duration: 200,
			},
		} );

	} );
	
}

function edd_repeater_reindex_primary() {
	
	jQuery( '[data-edd-rbm-repeater] .edd-rbm-repeater-item' ).each( function( index, row ) {
						
		var uuid = jQuery( row ).find( '[data-repeater-edit]' ).data( 'open' ),
			$modal = jQuery( '[data-reveal="' + uuid + '"]' );

		$modal.find( '[name]' ).each( function( inputIndex, input ) {

			var reindexed = jQuery( input ).attr( 'name' ).replace( /\[\d+\]/, '[' + index + ']' ); // Only replaces the first one as to not break Nested Repeaters

			jQuery( input ).attr( 'name', reindexed );

		} );

		init_edd_repeater_colorpickers( $modal );
		init_edd_repeater_select2( $modal );
		init_edd_repeater_tooltips( $modal );

	} );
	
}

// Repeaters
( function ( $ ) {

	// This only targets the top-level, primary repeater
	var $repeaters = $( '[data-edd-rbm-repeater]' );

	if ( ! $repeaters.length ) {
		return;
	}

	var edd_repeater_show = function() {

		// Hide current title for new item and show default title
		$( this ).find( '.repeater-header div.title' ).html( $( this ).find( '.repeater-header div.title' ).data( 'repeater-default-title' ) );
		
		// Nested Repeaters always inherit the number of Rows from the previous Repeater, so this will fix that.
		var repeater = $( this ).closest( '[data-edd-rbm-repeater]' ),
			nestedRepeaters = $( this ).find( '.edd-rbm-nested-repeater' );

		$( nestedRepeaters ).each( function( index, nestedRepeater ) {

			var items = $( nestedRepeater ).find( '.edd-rbm-repeater-item' ).get().reverse();

			if ( items.length == 1 ) return true; // Continue

			$( items ).each( function( row, nestedRow ) {

				if ( row == ( items.length - 1 ) ) return false; // Break

				$( nestedRow ).stop().slideUp( 300, function() {
					$( this ).remove();
				} );

				$( repeater ).trigger( 'edd-nested-repeater-cleanup', [$( nestedRow )] );

			} );

		} );
		
		init_edd_repeater_tooltips( this ); // This is necessary to ensure any Rows that are added have Tooltips

		$( this ).stop().slideDown();
		
		var repeater = $( this ).closest( '[data-edd-rbm-repeater]' );

		$( repeater ).trigger( 'edd-rbm-repeater-add', [$( this )] );

	}

	var edd_repeater_hide = function() {
		
		// For Nested Repeaters, just remove it. No Confirmation.
		if ( $( this ).closest( '.edd-rbm-repeater' ).hasClass( 'edd-rbm-nested-repeater' ) ) {
			
			$( this ).slideUp( 300, function () {
				$( this ).remove();
			} );
			
			return;
		}

		var repeater = $( this ).closest( '[data-edd-rbm-repeater]' ),
			confirmDeletion = confirm( eddFields.i18n.confirmDeletion );
			
		if ( confirmDeletion ) {

			var $row = $( this ),
				uuid = $row.find( '[data-repeater-edit]' ).data( 'open' ),
				$modal = $( '[data-reveal="' + uuid + '"]' ),
				templateIndex = $row.index();
			
			$.ajax( {
				'type' : 'POST',
				'url' : eddFields.ajax,
				'data' : {
					'action' : 'delete_edd_rbm_fields_template',
					'index' : templateIndex,
				},
				success : function( response ) {
					
					// Remove whole DOM tree for the Modal.
					$modal.parent().remove();

					// Remove DOM Tree for the Notification "Header"
					$row.stop();
					setTimeout( function() {
						
						$row.effect( 'highlight', { color : '#FFBABA' }, 300 ).dequeue().slideUp( 300, function () {
							$row.remove();
							edd_repeater_reindex_primary();
						} );
						
					} );

					$( repeater ).trigger( 'edd-rbm-repeater-remove', [$row] );
					
				},
				error : function( request, status, error ) {
					
				}
			} );

		}

	}

	$repeaters.each( function () {

		var $repeater = $( this ),
			$dummy = $repeater.find( '[data-repeater-dummy]' );

		// Repeater
		$repeater.repeater( {

			repeaters: [ {
				selector: '.edd-rbm-nested-repeater',
				show: edd_repeater_show,
				hide: edd_repeater_hide,
				ready: function ( setIndexes ) {
					$repeater.find( '.edd-rbm-repeater-list' ).on( 'sortupdate', setIndexes );
				}
			} ],
			show: edd_repeater_show,
			hide: edd_repeater_hide,
			ready: function ( setIndexes ) {
				// Custom Reindexing Function below
			}

		} );

		if ( $dummy.length ) {
			$dummy.remove();
		}
		
		$repeater.find( '.edd-rbm-repeater-list' ).sortable( {
			axis: 'y',
			handle: '[data-repeater-item-handle]',
			forcePlaceholderSize: true,
			update: function ( event, ui ) {
				
				// If we're not in a Nested Repeater
				if ( ! $( event.currentTarget ).hasClass( 'edd-rbm-nested-repeater' ) ) {
					
					edd_repeater_reindex_primary();
					
					// Grab all data with their new indexes to save
					var data = [];
					$( '[data-edd-rbm-repeater] .edd-rbm-repeater-item' ).each( function( index, row ) {
						
						var uuid = $( row ).find( '[data-repeater-edit]' ).data( 'open' ),
							$modal = $( '[data-reveal="' + uuid + '"]' );

						data.push( get_edd_fields_form( $modal[0] ) );
						
					} );
					
					$.ajax( {
						'type' : 'POST',
						'url' : eddFields.ajax,
						'data' : {
							'action' : 'sort_edd_fields_templates',
							'templates' : data,
						},
						success : function( response ) {

						},
						error : function( request, status, error ) {

						}
					} );
					
					// TODO: Save order changes to DB
					
				}
				else {
					init_edd_repeater_colorpickers( $( event.currentTarget ).closest( '.edd-rbm-repeater-content' ) );
					init_edd_repeater_select2( $( event.currentTarget ).closest( '.edd-rbm-repeater-content' ) );
					init_edd_repeater_tooltips( $( event.currentTarget ).closest( '.edd-rbm-repeater-content' ) );
				}
				
			}

		} );
		
		$( document ).on( 'closed.zf.reveal', '.edd-rbm-repeater-content.reveal', function() {
			
			var title = $( this ).find( 'td:first-of-type *[type!="hidden"]' ),
				uuid = $( this ).closest( '.edd-rbm-repeater-content.reveal' ).data( 'reveal' ),
				$row = $( '[data-open="' + uuid + '"]' );
			
			if ( $( title ).val() !== '' ) {
				$row.closest( '.edd-rbm-repeater-item' ).find( '.repeater-header div.title' ).html( $( title ).val() );
			}
			else {
				var defaultValue = $row.closest( '.edd-rbm-repeater-item' ).find( '.repeater-header div.title' ).data( 'repeater-default-title' );
				$row.closest( '.edd-rbm-repeater-item' ).find( '.repeater-header div.title' ).html( defaultValue );
			}
			
		} );

	} );

} )( jQuery );