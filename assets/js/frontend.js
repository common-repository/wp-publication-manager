jQuery(document).ready(function($){

	var $slider = $("#slider-range"),
	$rangeInput = $('.tor-range-input');

	var tooltip = $('<div id="tooltip" />').css({
	    position: 'absolute',
	    top: -25,
	    left: -10
	}).hide();

	$('#price-range-submit').hide();

	$("#min_price,#max_price").on('change', function () {

	  $('#price-range-submit').show();

	  var min_price_range = parseInt($("#min_price").val());

	  var max_price_range = parseInt($("#max_price").val());

	  if (min_price_range > max_price_range) {
		$('#max_price').val(min_price_range);
	  }

	  $slider.slider({
		values: [min_price_range, max_price_range]
	  });

	});


	$("#min_price,#max_price").on("paste keyup", function () {

	  $('#price-range-submit').show();

	  var min_price_range = parseInt($("#min_price").val());

	  var max_price_range = parseInt($("#max_price").val());

	  if(min_price_range == max_price_range){

			max_price_range = min_price_range + 100;

			$("#min_price").val(min_price_range);
			$("#max_price").val(max_price_range);
	  }

	  $slider.slider({
		values: [min_price_range, max_price_range]
	  });

	});

	  $slider.slider({
		range: true,
		orientation: "horizontal",
  		min: $slider.data('min'),
  		max: $slider.data('max'),
  		values: $slider.data('defaults'),
  		step: $slider.data('step'),

		slide: function (event, ui) {
		  if (ui.values[0] == ui.values[1]) {
			  return false;
		  }

		  $("#min_price").val(ui.values[0]);
		  $("#max_price").val(ui.values[1]);

		  tooltip.text(ui.value);
		}
	  }).find(".ui-slider-handle").append(tooltip).hover(function() {
	    tooltip.show()
	}, function() {
	    tooltip.hide()
	});
});