( function( $ ) {

    console.log( 'load' );
    //console.log( tinymce.ui.Widget );

    tinymce.ui.Select = tinymce.ui.TextBox.extend( {

        init: function(settings) {
            
            var self = this;
            self._super(settings);
            settings = self.settings;

        },

        /**
         * Renders the control as a HTML string.
         *
         * @method renderHtml
         * @return {String} HTML representing the control.
         */
        renderHtml: function() {
            var self = this, settings = self.settings, attrs, elm;

            attrs = {
                id: self._id,
                hidefocus: '1',
                class: 'mce-textbox mce-abs-layout-item mce-last',
            };

            tinymce.util.Tools.each([
                'required',
            ], function(name) {
                attrs[name] = settings[name];
            });

            if (self.disabled()) {
                attrs.disabled = 'disabled';
            }

            if (settings.subtype) {
                attrs.type = settings.subtype;
            }

            elm = document.createElement('select');
            for ( var id in attrs ) {
                elm.setAttribute( id, attrs[id] );
            }
            
            for ( var index = 0; index < settings.values.length; index++ ) {
                
                var value = settings.values[index].text;
                var key = settings.values[index].value;
                
                elm.innerHTML += self.renderInnerHtml( key, value );
                
                //elm.innerHTML += '<option value="' + key + '">' + value + '</option>';
            }

            return elm.outerHTML;

        },
        
        renderInnerHtml: function( key, value ) {
            var self = this;
            
            // key is the value of our HTML object. Yes, it is confusing.
            if ( typeof key == 'object' ) {
                
                var output = '';
                
                for ( var index = 0; index < key.length; index++ ) {
                    
                    output += self.renderInnerHtml( key[index].value, key[index].text );
                    
                }
                
                return '<optgroup label="' + value + '">' + output + '</optgroup>';
                
            }
            else {
                return '<option value="' + key + '">' + value + '</option>';
            }
            
            return true;
            
        }
        
    } );

} )( jQuery );


console.log( tinymce.ui.Select );