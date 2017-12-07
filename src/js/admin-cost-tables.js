( function( $ ) {

	"use strict";

	var $table = $( "#cost-table-meta table" ),
		$add_column = $( "#cost-table-meta .add-column" ),
		$add_row = $( "#cost-table-meta .add-row" ),
		total_columns = 6;

	/**
	 * Hide the "Add Column" button if there are already the allowed maximum.
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
	 * This also disables the "Add Column" button once the allowed maximum is reached.
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
				var new_index = parseInt( value.match( /\d+/ ) ) + 1;
				return value.replace( /\d+/, new_index );
			} ).end()
			.appendTo( $table );
	} );

	/**
	 * Delete a row or column from the table.
	 */
	$table.on( "click", ".coa-meta-delete", function() {
		var $button = $( this );

		// Delete a row.
		if ( $button.hasClass( "delete-row" ) ) {
			var $row = $button.closest( "tr" );

			// Don't allow the first row to be deleted.
			if ( 0 === $row.index() ) {
				return;
			}

			// Delete the row.
			$row.remove();

			// Reindex remaining rows.
			$table.find( "tr" ).each( function() {
				var $current_row = $( this ),
					index = $current_row.index() - 1;

				$current_row.find( "input[type='text']" ).attr( "name", function( i, value ) {
					return value.replace( /\d+/, index );
				} ).end();
			} );
		}

		// Delete a column.
		if ( $button.hasClass( "delete-column" ) ) {
			var $column = $button.closest( "td" ).index();

			// Don't allow the first column to be deleted.
			if ( 0 === $column ) {
				return;
			}

			// Delete the column.
			$table.find( "tr" ).each( function() {
				$( this ).find( "td" ).eq( $column ).remove();
			} );

			// Reenable the add column button.
			$add_column.prop( "disabled", false );
		}
	} );

}( jQuery ) );
