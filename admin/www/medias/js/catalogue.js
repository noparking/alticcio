$(document).ready(function () {
	$("li.categories span").click(function() {
		$(this).siblings("ul.categories").toggle();
	});
	$("li.categories ul.categories").hide();
});
