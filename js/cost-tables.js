( function( $, cost_tables ) {
	"use strict";

	var $form = $( ".sfs-cost-tables" ),
		$session = $( "#cost-table-session" ),
		$campus = $( "#cost-table-campus" ),
		$career = $( "#cost-table-career" ),
		$table_container = $( ".cost-table-placeholder" );

	$form.on( "change", function( e ) {
		var input = e.target,
			name = $( input ).attr( "name" ),
			value = $( input ).val(),
			data = {
				action: "cost_tables",
				nonce: cost_tables.nonce,
				session: $session.val(),
				campus: $campus.val(),
				career: $career.val(),
				updated: name
			};

		if ( "" !== value && "" !== $session.val() && "" !== $campus.val() && "" !== $career.val() ) {
			$table_container.html( '<div class="sfs-loading"></div>' );

			$.post( cost_tables.ajax_url, data, function( response ) {
				var response_data = $.parseJSON( response );

				$table_container.html( response_data );
			} );
		}
	} );
}( jQuery, cost_tables ) );
