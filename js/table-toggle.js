// Toggle the display of different tables on the Tuition & Expenses table.
(function ($) {

	'use strict';

	var $list = $('.dropdown-content'),
		$filter = $('.dropdown');

	// For all tables grouped together in a div, hide all but the first.
	$('div .tablepress-scroll-wrapper:not(:first-child)').hide();

	// When a link in the .dropdown-content element is clicked, show
	// the table with the corresponding id and hide all its siblings.
	$list.on('click', 'a', function (e) {
		e.preventDefault();

		var table = '#tablepress-' + this.hash.replace('#', '') + '-scroll-wrapper';

		$(table).show().siblings().hide();

		// Hide the list of links.
		$list.hide();
	});

	// Show the list of links when the cursor is over the .dropdown element.
	$filter.on('mouseover', function () {
		$(this).find('.dropdown-content').show();
	});

	// Hide the list of links when the cursor is not over the .dropdown element.
	$filter.on('mouseout', function () {
		$(this).find('.dropdown-content').hide();
	});
}(jQuery));
