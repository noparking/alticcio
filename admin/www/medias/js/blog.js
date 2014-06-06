$(document).ready(function () {
	$("li.themes input[type=checkbox]").click(function () {
		if ($(this).attr("checked")) {
			check_parents($(this));
		}
		else {
			uncheck_children($(this));
		}
	});

	$("select[name*=id_blog]").change(function () {
		form_action = "reload";
		var form = $(this).parents("form");
		form.submit();
	});

	$("#blog-vignette").click(function() {
		$(this).toggleClass("to-delete");
		var del = $("input#delete-blog-vignette").val();
		$("input#delete-blog-vignette").val(del == 1 ? 0 : 1);
	});
});

function check_parents(element) {
	element.parent().parent().parent().children("input[type=checkbox]").each(function () {
		$(this).attr("checked", "checked");
		check_parents($(this));
	});
}

function uncheck_children(element) {
	element.siblings("ul").children("li").children("input[type=checkbox]").each(function () {
		$(this).attr("checked", "");
		uncheck_children($(this));
	});
}
