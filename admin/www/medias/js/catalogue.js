$(document).ready(function () {
	$("li.categories span").click(function() {
		$(this).siblings("ul.categories").toggle();
	});
	$("li.categories ul.categories").hide();

	$(".symlink-catalogue").change(function() {
		var id_catalogues = $(this).val();
		var html = "";
		if (id_catalogues) {
			html = $("#categories-for-catalogue-" + id_catalogues).html();
		}
		$("#categories-for-catalogue").html(html);
	});
});
