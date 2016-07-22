function some_function() {
    
    var array = [
        { text: 'test', value: 1 },
        { text: 'test 2', value: 2 },
        { text: 'Group', value: [
            { text: 'test 4', value: 4 },
            { text: 'test 5', value: 5 },
        ] },
    ];
    
    return array;
    
}

jQuery( function( $ ) {

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
                                        label: 'Post ID (Select "None" to use the Current Post)',
                                        values: some_function()
                                    },
                                    {
                                        type: 'textbox',
                                        name: 'class',
                                        label: 'Wrapper Class (Optional)'
                                    },
                                ],
                                onPostRender: function() {
                                    for( var index = 0; index < this.items()[0].items().length; index++ ) {
            
                                        var id = this.items()[0].items()[index].items()[1]._id;

                                        if ( $( '#' + id ).hasClass( 'mce-listbox' ) ) {

                                            console.log( id );

                                        }

                                    }
                                },
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
                                        type: 'textbox',
                                        name: 'id',
                                        label: 'Post ID (Select "None" to use the Current Post)'
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
                                                            ( e.data.name !== undefined ? ' class="' + e.data.name + '"' : '' ) + 
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