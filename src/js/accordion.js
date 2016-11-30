( function( $ ) {

	"use strict";

	var $js_accordion = $( ".js-accordion" );

	// In any element with the .js-accordion class, add the aria-controls attribute to all h3 tags.
	// Add a corresponding id to any div immediately following an h3, and hide the div.
	$js_accordion.find( "h3" ).each( function( index ) {
		var content_id = "accordion-content-" + index;

		$( this ).attr( "aria-controls", content_id ).attr( "aria-expanded", "false" ).
			next( "div" ).attr( "id", content_id ).attr( "aria-hidden", "true" ).hide();
	} );

	// When an h3 is clicked, toggle the visibility of the div immediately following it.
	$js_accordion.on( "click", "h3", function() {
		if ( "false" === $( this ).attr( "aria-expanded" ) ) {
			$( this ).attr( "aria-expanded", "true" ).
				next( "div" ).attr( "aria-hidden", "false" ).slideDown( "fast" );
		} else {
			$( this ).attr( "aria-expanded", "false" ).
				next( "div" ).attr( "aria-hidden", "true" ).slideUp( "fast" );
		}
	} );
}( jQuery ) );
