(function ($) {

	'use strict';

	$('.home .wsuwp-content-syndicate-list').before('<a href="#" class="event-control prev">Previous</a>').after('<a href="#" class="event-control next">Next</a>');

	$('.event-control.prev').hide();

	$('.wsuwp-content-syndicate-wrapper').on('click', '.event-control', function (e) {
		e.preventDefault();

		var list = $('.wsuwp-content-syndicate-list'),
			events = $('.wsuwp-content-syndicate-event'),
			position = '',
			right_max = -(events.length * events.outerWidth() - list.width());

		if ($(this).hasClass('prev')){
			$('.wsuwp-content-syndicate-list').css('left', '+=' + events.outerWidth() + 'px');
		} else if ($(this).hasClass('next')){
			$('.wsuwp-content-syndicate-list').css('left', '-=' + events.outerWidth() + 'px');
		}

		position = parseInt(list.css('left'));

		if (0 <= position) {
			$('.event-control.prev').hide();
		} else {
			$('.event-control.prev').show();
		}

		if (right_max >= position) {
			$('.event-control.next').hide();
		} else {
			$('.event-control.next').show();
		}
	});
}(jQuery));
