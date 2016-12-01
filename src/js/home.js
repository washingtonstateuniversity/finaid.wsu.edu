( function( $ ) {

	"use strict";

	// Apply "ready" class to intro column after page is loaded to trigger animations.
	$( document ).on( "ready", function() {
		$( ".intro" ).addClass( "ready" );
	} );

	// Add Previous and Next controls for navigating the output of the Content Syndicate shortcode.
	$( ".home-calendar .wsuwp-content-syndicate-list" ).
		before( "<a href='#' class='event-control prev'>Previous</a>" ).
		after( "<a href='#' class='event-control next'>Next</a>" );

	// Hide the Previous control - we only want to show it when it"s needed.
	$( ".event-control.prev" ).hide();

	// Adjust the left offset of the Content Syndicate list accordingly when the controls are clicked.
	$( ".wsuwp-content-syndicate-wrapper" ).on( "click", ".event-control", function( e ) {
		e.preventDefault();

		var list = $( ".wsuwp-content-syndicate-list" ),
			event = $( ".wsuwp-content-syndicate-event" ),
			position = "",
			right_max = -( event.length * event.outerWidth() - list.width() );

		if ( $( this ).hasClass( "prev" ) ) {
			list.css( "left", "+=" + event.outerWidth() + "px" );
		} else if ( $( this ).hasClass( "next" ) ) {
			list.css( "left", "-=" + event.outerWidth() + "px" );
		}

		position = parseInt( list.css( "left" ) );

		// Show the controls when list items are available to scroll to, and hide them otherwise.
		if ( 0 <= position ) {
			$( ".event-control.prev" ).hide();
		} else {
			$( ".event-control.prev" ).show();
		}

		if ( right_max >= position ) {
			$( ".event-control.next" ).hide();
		} else {
			$( ".event-control.next" ).show();
		}
	} );
}( jQuery ) );
