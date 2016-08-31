(function ($) {

	'use strict';

	$('.dropdown-content').on('click', 'a', function (e) {
		e.preventDefault();

		var table = '#tablepress-' + this.hash.replace('#', '');

		$(table).show().siblings().hide();
		$('.dropdown-content').hide();
	});

	$('.dropdown').on('mouseover', function () {
		$(this).find('.dropdown-content').show();
	});

	$('.dropdown').on('mouseout', function () {
		$(this).find('.dropdown-content').hide();
	});

	$(document).ready(function () {
		$('[href="#7"]').triggerHandler('click');
		$('[href="#16"]').triggerHandler('click');
	});
}(jQuery));
