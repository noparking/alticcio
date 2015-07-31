$(document).ready(function () {
	$(".assets-import-select-all").click(function () {
		if ($(this).prop("checked")) {
			$(".assets-import-select").prop("checked", true);
		}
		else {
			$(".assets-import-select").prop("checked", false);
		}
	});
	$(".assets-import-select").click(function () {
		var all_checked = true;
		$(".assets-import-select").each(function() {
			if ($(this).prop("checked") == false) {
				all_checked = false;
				return;
			}
		});
		$(".assets-import-select-all").prop("checked", all_checked);
	});
	$(".assets-import-edit-selected").click (function() {
		$(".assets-import-edit-selected-form").show();
		return false;
	});
	$(".copy-all").change(function() {
		var name = $(this).attr('name');
		$("#copy-" + name).prop("checked", true);
	});
	$(".assets-import-copy-all").click (function() {
		$(".assets-import-edit-selected-form").find(".copy-all").each(function () {
			var my_class = $(this).attr("name");
			if ($("#copy-" + my_class).prop('checked')) {
				var my_val = $(this).val();
				var my_checked = $(this).prop('checked');
				var my_object = $(this);
				$(".assets-import-select:checked").parents("tr").each(function () {
					var object = $(this).find("." + my_class);
					if (object.attr('type') == 'checkbox') {
						object.prop('checked', my_checked);
					} else if (object.hasClass("multicombobox")) {
						object.multicombobox("values", my_object.multicombobox("values"));
					} else {
						object.val(my_val);
					}
				});
			}
		});
		$(".assets-import-edit-selected-form").hide();
		return false;
	});
});
