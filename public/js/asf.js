var ASF = function () {
	var slowSpeed = 500;
	var medSpeed  = 300;
	var fastSpeed = 100;
};

ASF.prototype.login = function (node) {
	var self = this;

	var node = $(node);

	var username = node.find('input[name="username"]').val();
	var password = node.find('input[name="password"]').val();

	$.post('/login', {
		username: username,
		password: password
	}).done(function (response) {

		$.get('/partial/userbox', function (html) {
			$('#account-control .content').html(html);
		});

	}).fail(function (response) {
		console.log(response)
		return self.error(response.responseText);
	});

};

ASF.prototype.error = function (message) {
	this.message(message, true);
}

ASF.prototype.message = function (message, error) {
	var type = 'info';

	if (error) {
		type = 'danger';
	}

	var el = $('<div />');
	el.addClass('alert alert-' + type);
	el.text(message);

	$('#message-block').append(el);

	el.delay(5000).fadeOut(this.medSpeed);
}