if ( tinymce.ui.RBMSelect === undefined ) {

	tinymce.ui.RBMSelect = tinymce.ui.TextBox.extend( {

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
			var self = this, settings = self.settings, attrs, element;

			attrs = {
				id: self._id,
				hidefocus: '1',
				class: 'mce-textbox mce-abs-layout-item mce-last',
			};

			tinymce.util.Tools.each( [
				'required',
			], function(name) {
				attrs[name] = settings[name];
			} );

			if ( self.disabled() ) {
				attrs.disabled = 'disabled';
			}

			if ( settings.subtype ) {
				attrs.type = settings.subtype;
			}

			if ( settings.classes ) {
				attrs.class = attrs.class + ' ' + settings.classes;
			}

			element = document.createElement('select');
			for ( var id in attrs ) {
				element.setAttribute( id, attrs[id] );
			}

			for ( var index = 0; index < settings.values.length; index++ ) {

				var text = settings.values[index].text;
				var value = settings.values[index].value;
				var selected = settings.value;

				element.innerHTML += self.renderInnerHtml( value, text, selected );

			}

			return element.outerHTML;

		},

		/**
		 * Renders the InnerHTML Recursively
		 *
		 * @method renderInnerHtml
		 * @return {String} HTML representing the control.
		 */
		renderInnerHtml: function( value, text, selected ) {
			var self = this;

			if ( typeof value == 'object' ) {

				var output = '';

				for ( var index = 0; index < value.length; index++ ) {

					// This will technically grab nested optgroups too. Most browsers just don't handle that well.
					output += self.renderInnerHtml( value[index].value, value[index].text, selected );

				}

				return '<optgroup label="' + text + '">' + output + '</optgroup>';

			}
			else {

				if ( selected == value ) {
					return '<option value="' + value + '" selected>' + text + '</option>';
				}
				else {
					return '<option value="' + value + '">' + text + '</option>';
				}

			}

			return true;

		}

	} );
	
	tinymce.ui.Factory.add( 'RBMSelect', tinymce.ui.RBMSelect );
	
}
else {
	console.warn( 'TinyMCE already has a Select Control Type called "RBMSelect" that is taking precedence over the one included in EDD Fields. This may cause issues.' );
}