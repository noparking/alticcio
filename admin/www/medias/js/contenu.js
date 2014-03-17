$(document).ready(function () {
	
	$("li.themes input[type=checkbox]").click(function () {
		if ($(this).attr("checked")) {
			check_parents($(this));
		}
		else {
			uncheck_children($(this));
		}
	});

	$(".form-blogpost-input-date").datepicker({
		showButtonPanel: true,
    onClose: function() {
      date = $(this).datepicker("getDate");
      id = $(this).attr('id').replace("-visible", "");
      if (date) {
        $('#' + id).val(date.getTime()/1000);
      }
    }
  });

	$("input[type=submit][class~=delete]").click(function () {
		if ($(this).attr('name') == form_action) {
			var answer = prompt(dico['ConfirmerSuppression']);
			if (!answer) {
				return false;
			}
			answer = answer.toLowerCase();
			if (answer == "yes" || answer == "oui" || answer == "y" || answer == "o") {
				form_action = $(this).attr("name");
				return true;
			}
			else {
				form_action = "default";
				return false;
			}
		}
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
