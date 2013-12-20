<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>
<script>
$(function () {

	$.fn.editor = function (options) {
		var width = $(this).width();
		var height = $(this).height();
		var buttons = $(options.buttons);

		var self = this;

		var el = $(this);

		$(this).focus();

		$(this).css({
			width: 1,
			height: 1,
			resize: 'none',
			border: 'none'
		});

		var el = $('<div />');
		el.css({
			height: height,
			width: width
		});
		el.insertAfter($(this));

		$(this).on('keyup', function () {
			el.html($(this).val());
		});

		buttons.each(function () {
			$(this).on('click', function () {
				addTag(self.el, $(this));
			})
		})

	};

	$('#reply').editor({
		buttons: '#buttons'
	});

});
</script>

<ul class="list-unstyled list-inline" id="buttons">
	<li>
		<a data-open="<b>" data-close="</b>" href="javascript:void(0)">
			Bold
		</a>
	</li>
</ul>
<textarea autofocus style="width:300px;height:150px;" id="reply"></textarea>