( function( $ ) {
	
	function edd_fields_get_posts() {
	
		var posts = [],
			url;
		
		if ( ! location.origin )
			location.origin = location.protocol + '//' + location.host;
		
		url = location.origin + ajaxurl; // This is globally available from the WP Backend
		
		var no_async = function() {
			
			var temp;
			$.ajax( {

				async: false,
				type: 'POST',
				url: url,
				data: {
					action: 'edd_fields_get_posts',
					current_post_type: typenow, // This is globally available from the WP Backend
				},
				success: function( response ) {
					temp = $.parseJSON( response );
				},
				error: function( error ) {
					temp = [ { 'text': 'Error. See Browser Console.', 'value': '' } ];
					console.error( error );
				}

			} );
			
			return temp;
			
		}();
		
		// Assign to result of our non-async AJAX
		posts = no_async;
		
		return posts;
	
	}
	
	function edd_fields_get_names( post_id ) {
		
		var names = [],
			url;
		
		names.push( { 'text': 'Choose a Field Name', 'value': '' } );
		
		if ( post_id !== undefined && post_id !== '' ) {
		
			if ( ! location.origin )
				location.origin = location.protocol + '//' + location.host;

			url = location.origin + ajaxurl; // This is globally available from the WP Backend

			var no_async = function() {

				var temp;
				$.ajax( {

					async: false,
					type: 'POST',
					url: url,
					data: {
						action: 'edd_fields_get_names',
						post_id: post_id, 
					},
					success: function( response ) {
						temp = $.parseJSON( response );
					},
					error: function ( error ) {
						temp = [ { 'text': 'Error. See Browser Console.', 'value': '' } ];
						console.error( error );
					}

				} );

				return temp;

			}();

			// Assign to result of our non-async AJAX
			names = no_async;
			
		}
		else {
			// If No Post is Chosen
			
			$( '#edd_fields_meta_box .edd-fields-template:visible tbody tr .edd-fields-key input' ).each( function( index, element ) {
				
				names.push( { 'text': $( element ).val(), 'value': $( element ).val() } );
				
			} );
			
		}
		
		if ( $( '.tinymce-edd-fields-names option' ).length == 0 ) {
			
			// Create initial instance
			return names;
			
		}
		else {
			
			$( '.tinymce-edd-fields-names' ).empty();
			
			var html = '';
			for ( var index = 0; index < names.length; index++ ) {
			
				html += '<option value="' + names[index].value + '">' + names[index].text + '</option>';
				
			}
			
			$( '.tinymce-edd-fields-names' ).html( html );
			
		}
		
	}
	
	function edd_fields_tinymce_chosen() {
		
		$( '.edd-fields-posts' ).each( function( index, select ) {

			var style = $( select ).attr( 'style' );

			$( select ).on( 'chosen:ready', function( event, chosen ) {

				$( select ).hide();
				$( select ).next( '.chosen-container' ).attr( 'style', style );

			} )
			.chosen( {
				inherit_select_classes: true,
				placeholder_text_single: edd_vars.one_option,
				placeholder_text_multiple: edd_vars.one_or_more_option,
			} );

		} );
		
	}

	$( document ).ready( function() {

		tinymce.PluginManager.add( 'edd_fields_shortcodes_script', function( editor, url ) {
			editor.addButton( 'edd_fields_shortcodes', {
				text: 'EDD Fields',
				icon: false,
				type: 'menubutton',
				menu: [ 
					{
						text: 'Create Fields Table',
						onclick: function() {
							editor.windowManager.open( {
								title: 'Add Fields Table',
								body: [
									{
										type: 'select',
										name: 'id',
										label: "Using This Post's Data:",
										classes: 'edd-fields-posts edd-select edd-select-chosen',
										values: edd_fields_get_posts(),
									},
									{
										type: 'textbox',
										name: 'class',
										label: 'Wrapper Class (Optional)'
									},
								],
								onPostRender: function( e ) {
									
									setTimeout( function () {
								
										edd_fields_tinymce_chosen();
										
									}, 100 );
									
								},
								onsubmit: function( e ) {
									editor.insertContent( '[edd_fields_table' + 
															( e.data.id !== undefined && e.data.id !== '' ? ' post_id="' + e.data.id + '"' : '' ) + 
															( e.data.class !== undefined && e.data.class !== '' ? ' class="' + e.data.class + '"' : '' ) + 
														 ']' );
								}

							} ); // Editor

						} // onclick
						
					}, // Fields Table
					{
						text: 'Get Field Value',
						onclick: function() {
							editor.windowManager.open( {
								title: "Retrieve a Field's Value by Name",
								body: [
									{
										type: 'select',
										name: 'id',
										label: "Using This Post's Data:",
										classes: 'edd-select edd-select-chosen edd-fields-posts edd-fields-get-names',
										values: edd_fields_get_posts(),
									},
									{
										type: 'select',
										name: 'name',
										label: 'Field Name',
										classes: 'tinymce-edd-fields-names',
										values: edd_fields_get_names( undefined ),
									},
								],
								onPostRender: function( e ) {
									
									setTimeout( function () {
									
										edd_fields_tinymce_chosen();
										
									}, 100 );

									$( '.edd-fields-get-names' ).on( 'change', function( event ) {

										edd_fields_get_names( $( this ).val() );

									} );
									
								},
								onsubmit: function( e ) {
									editor.insertContent( '[edd_field' + 
															( e.data.id !== undefined && e.data.id !== '' ? ' post_id="' + e.data.id + '"' : '' ) + 
															( e.data.name !== undefined && e.data.name !== '' ? ' name="' + e.data.name + '"' : '' ) + 
														 ']' );
								}

							} ); // Editor

						} // onclick
						
					}, // Get Field
					
				], // Menu

			} ); // addButton

		} ); // Plugin Manager

	} ); // Document Ready

} )( jQuery );