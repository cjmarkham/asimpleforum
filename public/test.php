<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>
<script>
$(function () {

	$.fn.graph = function (options) {
		var width = options.width;
		var height = options.height;

		var self = $(this);

		self.width = width;
		self.height = height;


		
	};

	$('#graph').graph({
		width: 640,
		height: 480,

		type: 'bar',

		data: {

		}
	});

});
</script>

<canvas id="graph"></canvas>