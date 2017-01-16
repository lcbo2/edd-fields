// Conditionally Load Select2
// Modified Source: https://gist.github.com/gists/902090/

var maybeLoadSelect2 = function() {

	var select2Script,
		select2Style;

	if ( ! ( typeof Select2 !== "undefined" && Select2 !== null ) ) {

		select2Script = document.createElement( 'script' );
		select2Script.type = 'text/javascript';
		select2Script.id = 'edd-fields-select2-js';
		select2Script.src = '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js';
		
		select2Style = document.createElement( 'link' );
		select2Style.rel = 'stylesheet';
		select2Style.media = 'all';
		select2Style.id = 'edd-fields-select2-css';
		select2Style.href = '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css';
		
		document.body.appendChild( select2Script );
		document.body.appendChild( select2Style );

		return false;

	}

};

if ( window.addEventListener ) {
	window.addEventListener( 'load', maybeLoadSelect2, false );
}
else if ( window.attachEvent ) {
	window.attachEvent( 'onload', maybeLoadSelect2 );
}