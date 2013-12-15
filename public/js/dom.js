var asf = new ASF();
$(function () {
	$(document.body).fadeIn();

	$(document).on('submit', '[data-event="submit"]', function (e) {
		var action = $(this).data('action');

		if (typeof asf[action] == 'function') {
			asf[action](this);
		} else {
			console.error('No function ASF.' + action);
		}
	});

	$(document).on('click', '[data-event="click"]', function (e) {
		var action = $(this).data('action');

		if (typeof asf[action] == 'function') {
			asf[action](this);
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
			var topicId = $(this).closest('.topic').attr('id').replace('topic-', '')

			if ($('#preview-' + topicId).length) {
				self.popover({
					html: true,
					content: $('#preview-' + topicId).text(),
					title: 'HIHI'
				});

				self.popover('show');
			} else {

				$.post('/post/get_first', {
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