$(document).ready(function () {
	$(".filter-allpage").change(function () {
		var checked = $(this).attr("checked");
		$(this).parents("form").find(".filter-selected").each(function () {
			if ($(this).attr("checked") != checked) {
				selectcount(checked ? 1 : -1);
				$(this).attr("checked", checked);
			}
		});
	});
	$("a.filter-action").click(function () {
		var name = $(this).attr('id').replace(/-.*/, "");
		var action = $(this).attr('id').replace(name +"-action-", "");
		$(this).parents("form").find("input#" + name + "-action-action").val(action);
		form_action = "filter";
		$(this).parents("form").submit();
		return false;
	});
	$("input.filter-action").click(function () {
		form_action = "filter";
	});
	$(".filter-selected").change(function () {
		if ($(this).attr("checked")) {
			selectcount(1);
		}
		else {
			selectcount(-1);
		}
	});
	$(".filter-element").keydown(function(event) {
        if (event.keyCode == '13') {
            form_action = "filter";
            $(this).parents("form").submit();
            return false;
        }
	});
	$(".filter-column").click(function(event) {
		var name = $(this).attr('id').replace(/-.*/, "");
		var id = $(this).attr('id').replace(name + "-column-", "");
		var column = $(this).parents("form").find("#" + name + "-sort-column");
		var order = $(this).parents("form").find("#" + name + "-sort-order");
		if (column.val() != id) {
			column.val(id);
			order.val("ASC");
		}
		else {
			if (order.val() == "ASC") {
				order.val("DESC");
			}
			else {
				order.val("ASC");
			}
		}
		form_action = "filter";
		$(this).parents("form").submit();
		return false;
	});

	if ($("input.date-input").length) {
		$("input.date-input").datepicker({
			showButtonPanel: true,
			onClose: function() {
				date = $(this).datepicker("getDate");
				id = $(this).attr('id').replace("-visible", "").replace(/\[/g, "\\[").replace(/\]/g, "\\]");
				if (date) {
					$('#' + id).val(date.getTime()/1000);
				}
			}
		});
		$("input.filter-date").change(function() {
			if (!$(this).val()) {
				id = $(this).attr('id').replace("-visible", "").replace(/\[/g, "\\[").replace(/\]/g, "\\]");
				$('#' + id).val("");
			}
		});
	}
});

var selectcount = function (i) {
	var count = parseInt($(".filter-selectcount").html());
	$(".filter-selectcount").html(count + i);
}
