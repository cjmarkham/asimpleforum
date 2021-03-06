$(function () {

	asf.elements.toggleUserVisibility();

	$(document.body).fadeIn();

	if (asf.user.data != null) {
		asf.notifications.findByUser(function (list) {
			list = JSON.parse(list);
			
			if (list.unread.length) {
				$('.notification-link').addClass('orange');

				$.post('/' + asf.config.board.base + 'partial/user/notificationDropdown/', {
					params: {
						notifications: list.unread
					}
				}).done(function (html) {
					$('.notifications-dropdown-list').html(html);
				});	
			}
		});
	}

	$('div#notifications-dropdown.dropdown.inline').on('shown.bs.dropdown', asf.notifications.markRead);

	var fileReader = new FileReader();

	$('input[name="avatar"]').on('change', function (e) {

		var file = e.target.files[0];
		var imageType = /image*/;

		var fileReader = new FileReader();

		if (file.type.match(imageType)) {

			fileReader.onload = function () {

				var form = document.getElementById('avatar-form');
				var formData = new FormData(form);

				$.ajax({
					url: '/' + asf.config.board.base + 'user/save/avatar/',
					data: formData,
					processData: false,
					contentType: false,
					type: 'POST'
				}).done(function () {
					$('#avatar-list img').attr('src', fileReader.result);
				}).fail(function (response) {
					asf.error(response.resposeText);
					return false;
				});
			};

			fileReader.readAsDataURL(file); 
		} else {
			asf.error('File must be an image.');
		}
	});

	$('[data-toggle="tab"]').each(function () {

		$(this).on('click', function () {
			var target = $(this).data('target').replace('#', '');

			var state = {settings: target};
			window.history.pushState(state, '', target);

		});

	});

	$('#alert-tabs p').on('click', function () {
		$('#alert-tabs p').removeClass('active');
		$(this).addClass('active');

		var index = $(this).attr('data-index');

		$('#alert-box .alert').fadeOut();

		var target = $('#alert-box .alert').get(index);
		$(target).fadeIn();
	});

	// User profile editing
	/*$('.editable').each(function () {
		$(this).on({
			click: function () {
				$(this).attr('contenteditable', true);
				$(this).focus();
			}, 
			blur: function () {
				var self = $(this);

				$(this).removeAttr('contenteditable');
				asf.elements.loader.append($(this));

				var action = $(this).data('action');

				var parts = action.split('.');
				var method = asf;

				if (parts.length === 1) {
					method = method[action];
				} else {
					$(parts).each(function () {
						method = method[this];
					});
				}

				if (typeof method == 'function') {
					method(this, function () {
						asf.elements.loader.remove(self);

						self.animate({
							backgroundColor: $.Color('#dff0d8')
						}, 300, function () {
							self.animate({
								backgroundColor: $.Color('transparent')
							}, 300);
						});
					});
				} else {
					console.error('No function ASF.' + action);
				}
			}
		});
	});
	*/

	var formDataCheck = window.FormData;

	$('.calander').datepicker();

	// Modal for attachment images
	$(document.body).on('click', '.post-attachment img', function () {
		var modal = $('#image-modal');

		// Set a new width for the modal
		// based on image dimensions
		modal.find('.modal-dialog').css({
			width: $(this).width() + 120
		});

		modal.find('img').attr('src', $(this).attr('src'));

		modal.find('#image-attachment-title').text($(this).attr('data-title'));
		modal.modal({
			show: true
		});
	});

	// Quick search typeahead
	var selection = null;

	var selector = $('#search-form-indicator option:selected');

	if (asf.forum) {
		var forum = asf.forum;
		selection = forum.id;
	}

	var engine = new Bloodhound({
		name: 'search',
		local: [],
		remote: '/' + asf.config.board.base + 'search/typeahead/%QUERY/' + selection + '/',
		datumTokenizer: function(d) { 
			return Bloodhound.tokenizers.whitespace(d.name); 
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace
	});

	engine.initialize();

	$('.typeahead').typeahead({
		minLength: 3,
		highlight: true
	}, {
		displayKey: 'name',
		source: engine.ttAdapter(),
		templates: {
			suggestion: function (result) {
				return '<a href="/' + asf.config.board.base + result.url + '">' + result.name + '</a>';
			}
		}
	});

	/*$('.typeahead').typeahead({
		minLength : 3,
		source: function(query, process) {
			var selection = null;

			var selector = $('#search-form-indicator option:selected');

			if (selector.val() === 'this' && asf.page.section === 'forums') {
				var forum = JSON.parse(asf.forum);
				selection = forum.id;
			}

			$.post('/' + asf.config.board.base + 'search/typeahead/', { 
				query: query,
				selection: selection
			}, function(results) {
				results = JSON.parse(results);
				var data = [];

				// Show forum name along side topic name if applicable
				for (var i in results) {
					var string = results[i].name;

					if (results[i].forum) {
						string += ' in <span class="forum-name">' + results[i].forum + '</span>'; 
					}

					data.push(string);
				}
				process(data);
			});
		}, 
		updater: function (item) {
			item = item.toLowerCase().replace(/ in <span class=\"forum-name\">(.*)<\/span>/, '');
			item = encodeURIComponent(item);
			
			var url = '/' + asf.config.board.base + 'search/' + item;
			var append = $('#search-form-indicator option:selected').val() && asf.page.section == 'forum' ? asf.forum.id : 'all';

			url += '/' + append;

			document.location = url;
			return item;
		},
		sorter: function (items) {
			items.unshift(this.query);

			for (var i = 0; i < items.length; i++) {
				items[i] = items[i].replace(/<(?!\/?span(?=>|\s.*>))\/?.*?>/g, '');
			}
			return items;
		}
	});*/

	$(document).on('submit', '[data-event="submit"]', function (e) {
		var action = $(this).data('action');

		var parts = action.split('.');
		var method = asf;

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
			console.error('No function ASF.' + action);
		}
	});

	$(document).on('click', '[data-event="click"]', function (e) {
		var action = $(this).data('action');
		var params = $(this).data('params');

		var parts = action.split('.');
		var method = asf;

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
			console.error('No function ASF.' + action);
		}
	});

	$(document).on('keyup', '[data-event="keyup"]', function (e) {
		var action = $(this).data('action');
		var params = $(this).data('params');

		var parts = action.split('.');
		var method = asf;

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
			console.error('No function ASF.' + action);
		}
	});

	$(document).on('change', '[data-event="change"]', function (e) {
		var action = $(this).data('action');
		var params = $(this).data('params');

		var parts = action.split('.');
		var method = asf;

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
			console.error('No function ASF.' + action);
		}
	});

	$(document).on('keyup', '[data-event="return"]', function (e) {

		if (e.keyCode !== 13) {
			return false;
		}

		var action = $(this).data('action');
		var params = $(this).data('params');

		var parts = action.split('.');
		var method = asf;

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
			console.error('No function ASF.' + action);
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

	$('#quick-reply-modal').on('shown.bs.modal', function () {
		var textarea = $('#quick-reply-modal').find('textarea');

		textarea.focus();

		if (textarea[0].setSelectionRange) {
			var length = textarea.val().length * 2;
			textarea[0].setSelectionRange(length, length);
		} else {
			textarea.val(textarea.val());
		}

		textarea.scrollTop(99999);
	});

	$('form').on('fail', function () {

		var button = $(this).find('button.submit');
		button.attr('disabled', false);

		button.find('.working').hide();
		button.find('span').show();

		return false;
	});

	$('.topic-name.preview').on({
		mouseenter: function () {

			var self = $(this);
			var topicId = $(this).closest('.topic').attr('id').replace('topic-', '');

			if ($('#preview-' + topicId).length) {
				self.popover({
					html: true,
					content: $('#preview-' + topicId).text(),
					title: 'HIHI'
				});

				self.popover('show');
			} else {

				$.post('/' + asf.config.board.base + 'post/getFirst/', {
					topicId: topicId
				}, function (response) {

					var previewEl = $('<div />').attr('id', 'preview-' + topicId);
					previewEl.addClass('preview-tooltip').html(response);
					$(document.body).append(previewEl);

					self.popover({
						html: true,
						content: response,
						title: 'HIHI'
					});

					self.popover('show');

				});
			}
		}, mouseleave: function () {
			$(this).popover('hide');
		}
	});
	
	$('[data-toggle="tooltip"]').tooltip({
		placement: $(this).data('placement')
	});		
	
	// Navbar placement
	$(document).on('scroll', function () {
		var amount = $(this).scrollTop();
		var scrolled = $('#main-nav').css('position') == 'fixed' ? true : false;
		var logo = $('#logo img').clone();

		if (amount >= 105) {
			if (!scrolled) {

				logo.addClass('hidden-xs hidden-sm').css({
					height: 30,
					width: 200,
					'float': 'left',
					marginRight: 20
				});

				var width = $(window).width() / 2;

				if (width < 700) {
					width = 700;
				}

				$('#main-nav ul').css('width', width + 'px');
				$('#main-nav').prepend(logo)
							.removeClass('container')
							.addClass('clearfix');

				$('#main-nav a.avatar').animate({
					width: 35,
					height: 35,
					marginTop: 20
				}, 100);

				$('#main-nav a.avatar img').animate({
					width: 35,
					height: 35
				}, 100);

				$('#main-nav').css({
					position: 'fixed',
					top: 0,
					left: '50%',
					marginLeft: -(1170 / 2)
				}).animate({
					width: '100%',
					left: 0,
					zIndex: 10,
					marginLeft: 0
				}, 200);
			}
		} else {
			if (scrolled) {

				$('#main-nav ul').removeAttr('style');

				logo.removeClass('hidden-xs hidden-sm').css({
					height: 52,
					width: 318,
					'float': 'none',
					marginRight: 0,
					marginTop: 0
				});

				$('#main-nav a.avatar').animate({
					width: 55,
					height: 55,
					marginTop: 0
				}, 100);

				$('#main-nav a.avatar img').animate({
					width: 55,
					height: 55
				}, 100);

				$('#main-nav img:first').remove();

				$('#main-nav').css({
					position: 'relative',
					left: 0,
					zIndex: 1,
					marginLeft: 0
				}).animate({
					width: '1170px',
					marginLeft: -15
				}, 200).addClass('container');
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