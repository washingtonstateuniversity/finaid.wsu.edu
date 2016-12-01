( function( $, cost_tables ) {
	"use strict";

	var $form = $( ".sfs-cost-tables" ),
		$session = $( "#cost-table-session" ),
		$campus = $( "#cost-table-campus" ),
		$career = $( "#cost-table-career" ),
		$campus_options = $campus.find( "option" ),
		$career_options = $career.find( "option" ),
		$table_container = $( ".cost-table-placeholder" );

	// Disable any options that won't yield a table if selected.
	function update_options( options, available ) {
		options.each( function() {
			if ( "" !== $( this ).val() ) {
				if ( -1 !== $.inArray( $( this ).val(), available ) ) {
					$( this ).prop( "disabled", false );
				}
			}
		} );
	}

	// Handle changes to the form.
	$form.on( "change", function( e ) {

		// Make sure all values are posted.
		$campus_options.prop( "disabled", false );
		$career_options.prop( "disabled", false );

		var input = e.target,
			name = $( input ).attr( "name" ),
			data = {
				action: "cost_tables",
				nonce: cost_tables.nonce,
				session: $session.val(),
				campus: $campus.val(),
				career: $career.val(),
				updated: name
			};

		// Make the AJAX request if each input has a viable value.
		if ( "" !== $session.val() && "" !== $campus.val() && "" !== $career.val() ) {
			$table_container.html( "<div class='sfs-loading-indicator'></div>" );
			$campus_options.prop( "disabled", true );
			$career_options.prop( "disabled", true );

			$.post( cost_tables.ajax_url, data, function( response ) {
				var response_data = $.parseJSON( response );

				update_options( $campus_options, response_data.available_campuses );
				update_options( $career_options, response_data.available_careers );

				$table_container.html( response_data.table );
			} );
		}
	} );
}( jQuery, window.cost_tables ) );
