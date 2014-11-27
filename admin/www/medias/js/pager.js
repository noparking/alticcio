$(document).ready(function () {
	$(".pager-previous").click(function () {
		var i = parseInt($(this).siblings(".pager-page").val());
		$(this).siblings(".pager-page").val(i-1);
		form_action = "pager";
		$(this).parents("form").submit();
		return false;
	});
	$(".pager-next").click(function () {
		var i = parseInt($(this).siblings(".pager-page").val());
		$(this).siblings(".pager-page").val(i+1);
		form_action = "pager";
		$(this).parents("form").submit();
		return false;
	});
	$(".pager-number").change(function () {
		form_action = "pager";
		$(this).parents("form").submit();
		return false;
	});
	$(".pager-page").keydown(function(event) {
		if (event.keyCode == '13') {
			form_action = "pager";
			$(this).parents("form").submit();
			return false;
		}
	});
});
