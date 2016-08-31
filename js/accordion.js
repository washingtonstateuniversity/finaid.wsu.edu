(function ($) {

	'use strict';

	$('.js-accordion').find('h3').each(function (index) {
		var content_id = 'accordion-content-' + index;

		$(this).attr('aria-controls', content_id).attr('aria-expanded', 'false').
			next('div').attr('id', content_id).attr('aria-hidden', 'true').hide();
	});

	$('.js-accordion').on('click', 'h3', function () {
		if ('false' === $(this).attr('aria-expanded')) {
			$(this).attr('aria-expanded', 'true').
				next('div').attr('aria-hidden', 'false').slideDown('fast');
		} else {
			$(this).attr('aria-expanded', 'false').
				next('div').attr('aria-hidden', 'true').slideUp('fast');
		}
	});
}(jQuery));
