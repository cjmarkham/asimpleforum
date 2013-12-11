$(function () {

	$(document.body).fadeIn();

	var asf = new ASF();

	$(document).on('submit', '[data-event="submit"]', function (e) {
		var action = $(this).data('action');

		if (typeof asf[action] != undefined) {
			asf[action](this);
		}
	});

	$('date').timeago();

	$('form button').on('click', function (e) {
		if ($(this).attr('disabled')) {
			return false;
		}

		$(this).attr('data-text', $(this).text());
		$(this).attr('disabled', true);
		$(this).text('Working...');

		$(this).closest('form').submit();
	});

	$(document).on('scroll', function (e) {
		var amount = $(this).scrollTop();
		var scrolled = $('#main-nav').css('position') == 'fixed' ? true: false;
		var logo = $('#logo img').clone();

		if (amount >= 105) {
			if (!scrolled) {

				logo.css({
					height: 30,
					'float': 'left',
					marginRight: 20
				});

				$('#main-nav ul').css('width', 'auto');
				$('#main-nav').prepend(logo);

				$('#main-nav').css({
					position: 'fixed',
					top: 0,
					left: '50%',
					'border-radius': 0,
					marginLeft: -(1170 / 2) + 'px'
				}).animate({
					width: '100%',
					left: 0,
					marginLeft: 0
				}, 100);
			}
		} else {
			if (scrolled) {

				logo.css({
					height: 50,
					'float': 'none',
					marginRight: 0,
					marginTop: 0
				});

				$('#main-nav img').remove();

				$('#main-nav').css({
					position: 'relative',
					marginLeft: 0,
					left: 0,
					margin: '0 auto',
					'border-radius': '5px 5px 0 0'
				}).animate({
					width: '1170px'
				}, 100);
			}
		}
	});

	$('.user-link.resize').each(function () {
		var length = $(this).text().trim().length;

		if (length > 9) {
			$(this).css('font-size', '10px');
		}
	});
});