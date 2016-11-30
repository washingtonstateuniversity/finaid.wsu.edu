( function( $, document ) {
	"use strict";

	// Convert output from the WSUWP Table of Contents Generator into a `select` element.
	$( document ).ready( function() {
		var $toc = $( "#toc" ),
			items = $toc.find( "li" );

		items.unwrap().wrapAll( "<select></select>" ).closest( "select" ).prepend( "<option value=''>- Select Your Award -</option>" );

		$( items ).each( function() {
			var link = $( this ).children( "a" ),
				href = link.attr( "href" ),
				text = link.text();
			$( this ).replaceWith( "<option value=" + href + ">" + text + "</option>" );
		} );

		$toc.addClass( "select-wrap" );
	} );

	// When a title is selected, toggle the visibility of its respective section.
	$( "#toc" ).on( "change", "select", function() {
		var id = $( this ).val(),
			section = $( id ).closest( "section" );

		if ( "" === id ) {
			$( "section.hidden" ).hide();
		} else {
			$( section ).show().siblings( ".hidden" ).hide();
		}
	} );
}( jQuery, document ) );
