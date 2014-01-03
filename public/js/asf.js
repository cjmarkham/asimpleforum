var ASF = function () {
	var slowSpeed = 500;
	var medSpeed  = 300;
	var fastSpeed = 100;

	var user = null;
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

		$('#account-control').hide();
		$('#login-modal').modal('hide');

		$.get('/partial/userbox', function (html) {
			ASF.prototype.elements.replace('userbox', {}, '#account-control', function () {
				$('#account-control').fadeIn(ASF.prototype.medSpeed);

				var onlineCount = parseInt($('#sessions p:first span').text().trim(), 10);
				var guestCount = parseInt($('#sessions p:last span').text().trim(), 10);
				onlineCount += 1;
				guestCount -= 1;
				$('#sessions p:first span').text(onlineCount);
				$('#sessions p:last span').text(guestCount);

				$('#onlineList').fadeIn(ASF.prototype.medSpeed);

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
						$('#onlineList').hide().html(html).fadeIn(ASF.prototype.medSpeed);
					});
				}

				ASF.prototype.user = {
					username: username
				};
				ASF.prototype.elements.toggleUserVisibility();
				
			});
		});

	}).fail(function (response) {
		return ASF.prototype.error(response.responseText);
	});
};

ASF.prototype.logout = function (node) {
	var username = $(node).data('user');
	var self = this;

	$('#account-control').hide();

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

		$('.visible-user').fadeOut(ASF.prototype.medSpeed);

		ASF.prototype.elements.replace('userbox', {}, '#account-control', function () {
			$('#account-control').fadeIn(ASF.prototype.medSpeed);
		});

		ASF.prototype.user = undefined;
		ASF.prototype.elements.toggleUserVisibility();
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
			ASF.prototype.hideQuickReply();
		} else {

			var postCount = $('#posts .post').length;

			if (postCount >= parseInt(ASF.prototype.config.board.posts_per_page, 10)) {

				var url = location.href;
				if (location.hash) {
					var url = location.href.replace(location.hash, '');
				}

				url = url.replace(/\/([a-zA-Z]+)\-([0-9]+)\/([0-9]+)/, '/$1-$2/');
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
				ASF.prototype.hideQuickReply();
				ASF.prototype.delegate();
			});
		}
		
	}).fail(function (response) {
		return ASF.prototype.error(response.responseText);
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
		return ASF.prototype.message(response);
	}).fail(function (response) {
		return ASF.prototype.error(response.responseText);
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
	var locked = node.find('input[name="locked"]:checked').val();
	var sticky = node.find('input[name="sticky"]:checked').val();

	$.post('/topic/add_topic', {
		forumId: forumId,
		title: title,
		content: content,
		locked: locked,
		sticky: sticky
	}).done(function (response) {

		response = JSON.parse(response);
		
		$.post('/partial/topic', {
			params: {
				topic: {
					id: response.topic_id,
					name: title,
					views: 0,
					replies: 0,
					lastPostName: title,
					lastPosterUsername: response.author,
					lastPostTime: Math.round(new Date().getTime() / 1000),
					lastPostId: response.post_id,
					author: response.author,
					content: response.content,
					added: Math.round(new Date().getTime() / 1000),
					updated: Math.round(new Date().getTime() / 1000),
					locked: response.locked,
					sticky: response.sticky
				},
				forum: {
					name: response.forum_name
				}
			}
		}).done(function (html) {
			node.find('input[name="name"]').val('');
			node.find('textarea').val('');

			ASF.prototype.hideModal(node);

			var stickies = $('#topics .sticky').length;

			var seperator = $('<div />').addClass('seperator');
			
			var topicCount = $('#topics .topic').length;

			if (stickies > 0) {
				$(seperator).insertAfter('#topics .sticky:last');
				$(html).insertAfter(seperator);
			} else {

				if (topicCount == 0) {
					$('#topics .content').html(html);
				} else {
					$('#topics .content').prepend(html, seperator);
				}
			}

			ASF.prototype.delegate();
		});

	}).fail(function (response) {
		node.trigger('fail');
		ASF.prototype.error(response.responseText);
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

		CKEDITOR.instances.reply.setData('<blockquote><span>' + author + '</span><p>' + response.content + '</p></blockquote><p id="end"></p>');

		var modal = $('#quick-reply-modal');
		modal.modal({
			show: true
		});

		modal.on('shown.bs.modal', function () {
			var editor = CKEDITOR.instances.reply;
			var range = editor.createRange();

			range.moveToElementEditEnd(range.root);
			editor.getSelection().selectRanges([range])

			editor.focus();	
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

		ASF.prototype.elements.replace('postLikes', {
			post: {
				likes: JSON.parse(response),
				id: postId
			}
		}, '.like-row[data-postId="' + postId + '"]');

	}).fail(function (response) {
		ASF.prototype.error(response.responseText);
		return false;
	});
}

ASF.prototype.elements = function () {};

ASF.prototype.elements.replace = function (element, params, replace, callback) {

	if (!$(replace).length) {
		console.error('Could not find element', replace);
		return false;
	}

	replace = $(replace);

	$.post('/partial/' + element, {
		params: params
	}).done(function (response) {
		replace.html(response);

		if (typeof callback == 'function') {
			callback()
		}
	});
};

ASF.prototype.elements.toggleUserVisibility = function () {
	console.log(ASF.prototype.user);

	if (typeof ASF.prototype.user == 'undefined') {
		$('.hidden-no-user').hide();
		$('.hidden-user').show();
	} else {
		$('.hidden-no-user').show();
		$('.hidden-user').hide();
	}
};

ASF.prototype.profile = function () {};

ASF.prototype.profile.addComment = function (node) {
	var self = ASF.prototype;

	var node = $(node);

	var userId = node.find('[name="profileId"]').val();
	var comment = node.find('textarea').val();

	$.post('/user/addComment', {
		profileId: userId,
		comment: comment
	}).done(function (response) {
		response = JSON.parse(response);

		$.post('/partial/profileComment', {
			params: {
				comment: {
					added: response.added,
					comment: response.comment,
					username: response.username,
					likes: 0,
					comments: 0
				},
				profile: {
					id: response.profileId
				}
			}
		}).done(function (html) {

			if (!$('.profile-comment').length) {
				node.next().html(html);
			} else {
				node.next().prepend(html);
			}
		});

	}).fail(function (response) {
		ASF.prototype.error(response.responseText);
		return false;
	});
};