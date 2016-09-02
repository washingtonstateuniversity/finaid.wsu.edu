// Toggle the display of different tables on the Tuition & Expenses table.
(function ($) {

	'use strict';

	// For all tables grouped together in a div, hide all but the first.
	$('div table:not(:first-child)').hide();

	// When a link in the .dropdown-content element is clicked, show
	// the table with the corresponding id and hide all its siblings.
	$('.dropdown-content').on('click', 'a', function (e) {
		e.preventDefault();

		var table = '#tablepress-' + this.hash.replace('#', '');

		$(table).show().siblings().hide();

		// Hide the list of links.
		$('.dropdown-content').hide();
	});

	// Show the list of links when the cursor is over the .dropdown element.
	$('.dropdown').on('mouseover', function () {
		$(this).find('.dropdown-content').show();
	});

	// Hide the list of links when the cursor is not over the .dropdown element.
	$('.dropdown').on('mouseout', function () {
		$(this).find('.dropdown-content').hide();
	});
}(jQuery));
