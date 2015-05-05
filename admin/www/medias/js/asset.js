$(document).ready(function () {
	$("th.assets-import-infos-switch").click(function() {
		if ($(this).hasClass("activated")) {
			$(this).removeClass("activated");
			$(".assets-import-infos-form").hide();
			$(".assets-import-infos-noform").show();
		}
		else {
			$(this).addClass("activated");
			$(".assets-import-infos-form").show();
			$(".assets-import-infos-noform").hide();
		}
	});
	$("a.assets-import-infos-switch").click(function() {
		$(this).parents(".assets-import-infos").find(".assets-import-infos-form,.assets-import-infos-noform").toggle();
		$(this).parents(".assets-import-infos").find("select").each(function() {
			if (!$(this).hasClass("multiselect")) {
				$(this).addClass("multiselect").multiselect();
			}
		});
		return false;
	});
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
	$(".assets-import-copy-all").click (function() {
		$(".assets-import-edit-selected-form").find(".copy-all").each(function () {
			var my_class = $(this).attr("name");
			var my_val = $(this).val();
			var my_checked = $(this).prop('checked');
			$(".assets-import-select:checked").parents("tr").each(function () {
				var object = $(this).find("." + my_class);
				if (object.attr('type') == 'checkbox') {
					object.prop('checked', my_checked);
				} else {
					object.val(my_val);
				}
				$(this).find(".multiselect." + my_class).multiselect("destroy").multiselect();
			});
		});
		$(".assets-import-edit-selected-form").hide();
		return false;
	});
});
