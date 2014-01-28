$(function () {

	$(document.body).fadeIn();

	$(document).on('submit', '[data-event="submit"]', function (e) {
		var action = $(this).data('action');

		var parts = action.split('.');
		var method = admin;

		if (parts.length === 1) {
			method = method[action];
		} else {
			$(parts).each(function () {
				method = method[this];
			});
		}

		if (typeof method == 'function') {
			method(this);
		} else {
			console.error('No function Admin.' + action);
		}
	});

	$(document).on('click', '[data-event="click"]', function (e) {
		var action = $(this).data('action');
		var params = $(this).data('params');

		var parts = action.split('.');
		var method = admin;

		if (parts.length === 1) {
			method = method[action];
		} else {
			$(parts).each(function () {
				method = method[this];
			});
		}

		if (typeof method == 'function') {
			method(this, params);
		} else {
			console.error('No function Admin.' + action);
		}
	});

	$('date').timeago();

	$('form').on('submit', function (e) {
		if ($(this).attr('disabled')) {
			return false;
		}
		var button = $(this).find('button.submit');

		button.attr('disabled', true);
		button.find('span').hide();

		if (button.find('strong').length) {
			button.find('strong').show();
		} else {
			button.append('<strong class="working">Working...</strong>');
		}
	});

	$('[data-toggle="popover"]').popover({
		trigger: 'click'
	});
});