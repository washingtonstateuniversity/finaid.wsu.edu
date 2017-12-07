( function( $ ) {

	"use strict";

	var $table = $( "#cost-table-meta table" ),
		$add_column = $( "#cost-table-meta .add-column" ),
		$add_row = $( "#cost-table-meta .add-row" ),
		total_columns = 6;

	/**
	 * Hide the "Add Column" button if there are already six columns.
	 */
	if ( total_columns <= $table.find( "tr:first td" ).length ) {
		$add_column.prop( "disabled", true );
	}

	/**
	 * Add a column to the table.
	 *
	 * This clones the last cell in each row,
	 * removes the value from the input,
	 * and appends the cell to the row.
	 *
	 * This also disables the "Add Column" button once there are six total columns.
	 */
	$add_column.on( "click", function() {
		if ( total_columns > $table.find( "tr:first td" ).length ) {
			$table.find( "tr" ).each( function() {
				$( this ).find( "td" ).last().clone()
				.find( "input" ).val( "" ).end()
				.appendTo( $( this ) );
			} );
		}

		if ( total_columns <= $table.find( "tr:first td" ).length ) {
			$add_column.prop( "disabled", true );
		}
	} );

	/**
	 * Add a row to the table.
	 *
	 * This clones the last row in the table,
	 * removes the values from the inputs,
	 * and increments the name attribute index.
	 */
	$add_row.on( "click", function() {
		$table.find( "tr" ).last().clone()
			.find( "input[type='text']" ).val( "" ).end()
			.find( "input[type='text']" ).attr( "name", function( i, value ) {
				var $index = value.match( /\d+/ ),
					$new_index = parseInt( $index ) + 1;
				return value.replace( /\d+/, $new_index );
			} ).end()
			.appendTo( $table );
	} );

}( jQuery ) );
