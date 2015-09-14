(function($) {
	$.fn.dynamicfieldsets = function(selector, replacements) {
		this.each(function () {
			var element = $(this);
			var select = element.find(selector);
			var original_select = select.clone();
			var fieldset = element.find(".dynamicfieldset");
			var fieldsets = {};
			
			select.change(function() {
				var key = $(this).val();
				var value = $(this).find(":selected").text();
				if (key != 0) {
					var html = fieldset.html();
					for (i in replacements) {
						var re = new RegExp(i, 'g')
						html = html.replace(re, replacements[i]);
					}
					html = html.replace(/KEY/g, key);
					html = html.replace(/VALUE/g, value);
					var new_fieldset = $(html);
					new_fieldset.data("id_fieldset", key);
					new_fieldset.find(".delete-fieldset").click(function () {
						var id_fieldset = new_fieldset.data("id_fieldset");
						new_fieldset.remove();
						delete fieldsets[id_fieldset];
						restore_select();
						return false;
					});
					$(this).parent().append(new_fieldset);
					$(this).find(":selected").remove();
					$(this).val(0);
					$(this).blur();
					fieldsets[key] = key;
				}
			});

			function restore_select() {
				select.html(original_select.html());
				for (i in fieldsets) {
					select.find('option[value="' + i + '"]').remove();
				}
			}

			return this;
		});
	};
})(jQuery)
