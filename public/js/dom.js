$(function () {

	asf.elements.toggleUserVisibility();

	$(document.body).fadeIn();

	var formDataCheck = window.FormData;

	// Attachment selection for quick reply
	/*$('#attachments input').on('change', function () {
		var files = $(this)[0].files;

		$('#attachment-list').empty();

		for (var i = 0; i < files.length; i++) {
			var fileReader = new FileReader();
			var file = files[i];
			fileReader.readAsDataURL(file);
			
			var container = $('<div />');
			var p = $('<p />').text(file.name).addClass('inline');
			var a = $('<a />').text('Place inline').addClass('inline btn btn-orange btn-xs');

			container.append(p, a);

			a.on('click', function () {
				var editor = CKEDITOR.instances.reply;

				editor.insertElement(CKEDITOR.dom.element.createFromHtml('<img src="' + fileReader.result + '" alt="" title="" />'));

			});	

			$('#attachment-list').append(container);
		}

	});*/

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
	$('.typeahead').typeahead({
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
	});

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

				$('#main-nav ul').css('width', width + 'px').addClass('pull-left');
				$('#main-nav').prepend(logo)
							.removeClass('container')
							.addClass('clearfix');

				$('#main-nav').css({
					position: 'fixed',
					top: 0,
					left: '50%',
					'border-radius': 0,
					marginLeft: -(1170 / 2) + 'px'
				}).animate({
					width: '100%',
					left: 0,
					zIndex: 10,
					marginLeft: 0
				}, 100);
			}
		} else {
			if (scrolled) {

				logo.removeClass('hidden-xs hidden-sm').css({
					height: 52,
					width: 318,
					'float': 'none',
					marginRight: 0,
					marginTop: 0
				});

				$('#main-nav img').remove();

				$('#main-nav').css({
					position: 'relative',
					left: 0,
					margin: '0 auto',
					zIndex: 1,
					'border-radius': '5px 5px 0 0'
				}).animate({
					width: '1170px'
				}, 100).addClass('container');
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