var admin = {

	forums: {

		addParent: function (node) {

			node = $(node);

			var name = node.find('input[name="name"]').val();
			var placement = node.find('input[name="placement"]:checked').val();
			var position = node.find('select[name="position"] option:selected').val();

			$.post('/forum/add', {
				name: name,
				parent: 0,
				placement: placement,
				position: position
			}).done(function () {
				window.location.reload();
			}).fail(function (response) {
				admin.error(response.responseText);
			});

		},

		addChild: function (node) {
			node = $(node);

			var parent = node.find('input[name="parent"]').val();
			var name = node.find('input[name="name"]').val();
			var description = node.find('input[name="description"]').val();
			var placement = node.find('input[name="placement"]:checked').val();
			var position = node.find('input[name="position"] option:selected').val();

			$.post('/forum/add', {
				parent: parent,
				name: name,
				description: description,
				placement: placement,
				position: position
			}).done(function () {
				//window.location.reload();
			}).fail(function (response) {
				admin.error(response.responseText);
			});
		},

		delete: function (node) {
			node = $(node);

			var forumId = node.attr('data-forumId');

			if (!forumId) {
				return false;
			}

			$.post('/forum/delete', {
				id: forumId
			}).done(function () {
				$('#forum-' + forumId).remove();
			}).fail(function (response) {
				admin.error(response.responseText);
			})
		}

	},

	error: function (message) {
		this.message(message, true);
	},

	message: function (message, error) {
		var type = 'info';

		if (error) {
			type = 'danger';
		}

		var el = $('<div />');
		el.addClass('alert alert-' + type);
		el.html(message);

		$('#message-block').append(el);

		el.delay(5000).fadeOut(this.medSpeed);
	}

};