$(document).ready(function () {
	$(".produit-section-item").click(function () {
		$(".produit-section-item").removeClass("selected");
		$(this).addClass("selected");
		$("fieldset.produit-section").addClass("hidden");
		var id = $(this).attr('id').replace('-item', '');
		var section = id.replace('produit-section-', '');
		$(this).parents("form").find("input[name=section]").val(section);
		$("." + id).removeClass("hidden");
	});
	
	$("input[type=submit][class~=delete]").click(function () {
		if ($(this).attr('name') == form_action) {
			if (confirm(dico['ConfirmerSuppression'])) {
				form_action = $(this).attr("name");
				return true;
			}
			else {
				form_action = "default";
				return false;
			}
		}
	});

	$(".form-edit-input[type!=submit]").focus(function () {
		var id = $(this).parents("fieldset").attr('id');
		if (id == "produit-section-images-new") {
			form_action = "add-image";
		}
		else if (id == "produit-section-options-new") {
			form_action = "add-option";
		}
		else {
			form_action = "default";
		}
	});

	$("table.sortable").tableDnD({
		onDragClass: "dragged",
		onDrop: function(table, row) {
			var classement = 0;
			$(table).find("input[name*=classement]").each(function () {
				$(this).val(classement);
				classement++;
			});
		}
	});

	$("select[name*=id_application]").change(function () {
		form_action = "reload";
		var form = $(this).parents("form");
		if (form.find("input[type=hidden][name*=produit][name*=id]").length) {
			form.submit();
		}
	});

	$("select[name*=id_types_attributs]").change(function () {
		form_action = "reload";
		var form = $(this).parents("form");
		if (form.find("input[type=hidden][name*=attribut][name*=id]").length) {
			form.submit();
		}
	});

	$("select[name*=hd_extension]").change(function () {
		var extension = $(this).val();
		$(this).parent().find('.nom_hd').each(function () {
			if (extension) {
				var nom = $(this).attr("name") + "." + extension;
				$(this).val(nom);
				$(this).show();
				$(this).select();
			}
			else {
				$(this).hide();
			}
		});
	});

	$(".nom_hd").click(function () {
		$(this).select();
	});
});
