jQuery( function( $ ) {
    
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
                error: function ( error ) {
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

    $( document ).ready( function() {

        var good = '';

        $( document ).on( 'keyup', '.mce-numbers-only', function( event ) {

            var input = $( this );

            if ( event.which !== 8 ) { // If not backspace
                var matchedPosition = input.val().search( /[a-z@#!$%,-^&*()_+|~=`{}\[\]:";'<>?.\/\\]/i );
                if( matchedPosition === -1 ) {
                    input.val( good );
                }
                else{
                    good = input.val();
                }

            }

            if ( input.val() === '0' ) {
                input.val( '' );
            }

        } );

        $( document ).on( 'keyup', '.mce-letters-only', function( event ) {
            var input = $( this );
            var matchedPosition;

            if ( event.which !== 8 ) { // If not backspace

                if ( input.hasClass( 'mce-no-spaces' ) ) {
                    matchedPosition = input.val().search( /^[a-z]*$/i );
                }
                else{
                    matchedPosition = input.val().search( /^[a-z ]*$/i );
                }

                if( matchedPosition === -1 ) {
                    input.val( good );
                }
                else{
                    good = input.val();
                }

            }

        } );

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
                                        values: edd_fields_get_posts(),
                                    },
                                    {
                                        type: 'textbox',
                                        name: 'class',
                                        label: 'Wrapper Class (Optional)'
                                    },
                                ],
                                onsubmit: function( e ) {
                                    editor.insertContent( '[edd_fields_table' + 
                                                            ( e.data.id !== undefined ? ' post_id="' + e.data.id + '"' : '' ) + 
                                                            ( e.data.class !== undefined ? ' class="' + e.data.class + '"' : '' ) + 
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
                                        values: edd_fields_get_posts(),
                                    },
                                    {
                                        type: 'textbox',
                                        name: 'name',
                                        label: 'Field Name'
                                    },
                                ],
                                onsubmit: function( e ) {
                                    editor.insertContent( '[edd_field' + 
                                                            ( e.data.id !== undefined ? ' post_id="' + e.data.id + '"' : '' ) + 
                                                            ( e.data.name !== undefined ? ' name="' + e.data.name + '"' : '' ) + 
                                                         ']' );
                                }

                            } ); // Editor

                        } // onclick
                        
                    }, // Get Field
                    
                ], // Menu

            } ); // addButton

        } ); // Plugin Manager

    } ); // Document Ready

} );