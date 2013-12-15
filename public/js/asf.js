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
			$('#account-control .content').fadeOut(function () {
				$(this).html(html).fadeIn(self.medSpeed);
			});

			var onlineCount = parseInt($('#sessions p:first span').text().trim(), 10);
			var guestCount = parseInt($('#sessions p:last span').text().trim(), 10);
			onlineCount += 1;
			guestCount -= 1;
			$('#sessions p:first span').text(onlineCount);
			$('#sessions p:last span').text(guestCount);

			$('#onlineList').fadeIn(self.medSpeed);

			var link = '<a data-user="' + username + '" href="/user/' + username + '">' + username + '</a>';

			if ($('#users').length) {
				if ($('#users a').length) {
					$('#users').append(', ' + link);
				} else {
					$('#users').append(link);
				}
			} else {
				$.post('/partial/onlineList', {
					params: {
						sessions: {
							online: [link]
						}
					}
				}).done(function (html) {
					$('#onlineList').hide().html(html).fadeIn(self.medSpeed);
				});
			}
		});

	}).fail(function (response) {
		return self.error(response.responseText);
	});
};

ASF.prototype.logout = function (node) {
	var username = $(node).data('user');
	var self = this;

	$.get('/logout').done(function () {
		$('#users [data-user="' + username + '"]').remove();

		if ($('#users a').length == 0) {
			$('#sessions section').fadeOut();
		}

		var onlineCount = parseInt($('#sessions p:first span').text().trim(), 10);
		var guestCount = parseInt($('#sessions p:last span').text().trim(), 10);
		onlineCount -= 1;
		guestCount += 1;
		$('#sessions p:first span').text(onlineCount);
		$('#sessions p:last span').text(guestCount);

		$('.visible-user').fadeOut(self.medSpeed);

		$.get('/partial/userbox', function (html) {
			$('#account-control .content').fadeOut(function () {
				$(this).html(html).fadeIn(self.medSpeed);
			});
		});
	});

};

ASF.prototype.error = function (message) {
	this.message(message, true);
};

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
};

ASF.prototype.notification = function (image, title, message) {
	if (window.webkitNotifications.checkPermission() == 0) {
		window.webkitNotifications.createNotification(
			image, 
			title,
			message
		).show();
	}
};

ASF.prototype.showQuickReply = function () {
	$('#quick-reply-modal').modal({
		show: true
	})
};

ASF.prototype.hideQuickReply = function () {
	$('#quick-reply-modal').modal('hide');
	$('#quick-reply-modal textarea').val('');
}

ASF.prototype.addQuickReply = function (node) {
	var node = $(node);
	var self = this;
	var content = node.find('textarea').val();
	var name = node.find('input[name="name"]').val();
	var topicId = node.find('input[name="topicId"]').val();

	$.post('/post/add', {
		topicId: topicId,
		name: name,
		content: content
	}).done(function (response) {

		response = JSON.parse(response);

		if (response.updated) {
			$('#posts #post-' + response.id + ' .post-content').html(response.content);
			self.hideQuickReply();
		} else {

			var postCount = $('#posts .post').length;

			if (postCount >= parseInt(self.config.board.posts_per_page, 10)) {

				var url = location.href;
				if (location.hash) {
					var url = location.href.replace(location.hash, '');
				}

				console.log(location.href);
				
				url = url.substring(0, location.href.length - 1);
				url = url + response.page + '#' + response.id;

				window.location.href = url;
				return;
			}

			$.post('/partial/post', {
				params: {
					post: {
						id: response.id,
						name: name,
						content: content,
						added: Math.round(new Date().getTime() / 1000),
						username: response.username,
						poster: response.userId,
						edits: 0
					}
				}
			}).done(function (html) {
				var wrapper = $('<section />').attr('id', 'post-' + response.id).addClass('post');
				wrapper.append(html);
				$('#posts').append(wrapper);
				self.hideQuickReply();
				self.delegate();
			});
		}
		
	}).fail(function (response) {
		return self.error(response.responseText);
	});
};

ASF.prototype.delegate = function () {
	$('date').timeago();
};

ASF.prototype.reportPostTrigger = function (node) {
	var node = $(node);
	var postId = node.attr('data-postId');

	var modal = $('#post-report-modal');
	modal.find('input[name="postId"]').val(postId);

	modal.modal({
		show: true,
		keyboard: true
	});
};

ASF.prototype.reportPost = function (node) {
	var node = $(node);
	var self = this;

	var postId = node.find('input[name="postId"]').val();
	var reason = node.find('select[name="reason"] option:selected').val();

	if (!reason) {
		node.trigger('fail');
		return false;
	}

	$.post('/post/report', {
		postId: postId,
		reason: reason
	}).done(function (response) {
		$('#post-report-modal').modal('hide');
		return self.message(response);
	}).fail(function (response) {
		return self.error(response.responseText);
	});
};

ASF.prototype.hideModal = function (node) {
	var node = $(node);

	node.closest('.modal').modal('hide');
};

ASF.prototype.newTopicTrigger = function () {
	$('#create-topic-modal').modal('show');
};

ASF.prototype.addNewTopic = function (node) {
	var node = $(node);
	var self = this;

	var forumId = node.find('input[name="forumId"]').val();
	var title = node.find('input[name="name"]').val();
	var content = node.find('textarea').val();

	$.post('/topic/add_topic', {
		forumId: forumId,
		title: title,
		content: content
	}).done(function (response) {

		response = JSON.parse(response);
		
		$.post('/partial/topic', {
			params: {
				topic_id: response.topic_id,
				forum_id: response.forum_id,
				forum_name: response.forum_name,
				title: title,
				author: response.author,
				content: response.content,
				added: Math.round(new Date().getTime() / 1000)
			}
		}).done(function (html) {
			node.find('input[name="name"]').val('');
			node.find('textarea').val('');

			self.hideModal(node);

			var stickies = $('#topics .sticky').length;

			if (stickies) {
				var seperator = $('<div />').addClass('seperator');
				$(seperator).insertAfter('#topics .sticky:last');
				$(html).insertAfter(seperator);
			} else {
				$('#topics').prepend(html);
			}
		});

	}).fail(function (response) {
		node.trigger('fail');
		self.error(response.responseText);
		return false;
	})
};

ASF.prototype.editPost = function (node) {
	var node = $(node);

	var postId = node.attr('data-postId');

	$.post('/post/find_by_id', {
		id: postId
	}).done(function (response) {
		response = JSON.parse(response);

		var content = response.content;
		var element = node.parent().parent().parent().next();
		element.attr('contenteditable', true);

		var height = element.height();

		element.html(content).focus().css({
			height: height
		}).on('blur', function () {
			
			var el = $(this);
			var content = $(this).text();

			$.post('/post/update', {
				id: postId,
				content: content
			}).done(function (response) {
				response = JSON.parse(response);
				el.html(response.content);
			});	

		});
	});
}

ASF.prototype.quotePost = function (node) {
	var node = $(node);
	var postId = node.attr('data-postId');
	var author = node.attr('data-author');
	
	var self = this;

	$.post('/post/find_by_id', {
		id: postId
	}).done(function (response) {
		response = JSON.parse(response);

		var modal = $('#quick-reply-modal');
		modal.find('textarea').val('[quote=' + author + ']' + response.content.replace(/<br \/>/g, "\n") + "[/quote]\n");

		modal.modal({
			show: true
		});
	});
}

ASF.prototype.likePost = function (node) {
	node = $(node);
	var self = this;

	var postId = node.attr('data-postId');

	$.post('/post/like', {
		postId: postId
	}).done(function (response) {

		self.elements.replace('postLikes', {
			post: {
				likes: JSON.parse(response),
				id: postId
			}
		}, '.like-row[data-postId="' + postId + '"]');

	}).fail(function (response) {
		self.error(response.responseText);
		return false;
	});
}

ASF.prototype.elements = function () {};

ASF.prototype.elements.replace = function (element, params, replace) {

	if (!$(replace).length) {
		console.error('Could not find element', replace);
		return false;
	}

	replace = $(replace);

	$.post('/partial/' + element, {
		params: params
	}).done(function (response) {
		replace.html(response);
	});
};