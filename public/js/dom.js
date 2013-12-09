$(function () {

	var asf = new ASF();

	$(document).on('submit', '[data-event="submit"]', function (e) {
		var action = $(this).data('action');

		if (typeof asf[action] != undefined) {
			asf[action](this);
		}
	});

});