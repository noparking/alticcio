(function($) {
	$.widget("custom.multicombobox", {
		_create: function() {
			this.wrapper = $("<span>")
			.addClass("custom-multicombobox")
			.appendTo(this.element);

			this.list = [];
			if (multicombobox_list[this.element.attr('list')]) {
				this.list = multicombobox_list[this.element.attr('list')];
			}
			this.items = [];
			if (this.element.attr('items') && this.element.attr('items') != "") {
				this.items = this.element.attr('items').split(",");
			}
			if (this.element.attr('limit') && this.element.attr('limit') != "") {
				this.limit = this.element.attr('limit');
			}
			this.input_name = this.element.attr('name');

			this.selected = [];

			this._createAutocomplete();
			this._createShowAllButton();
			this._createSelectedList();
		},

		_createAutocomplete: function() {
			this.input = $("<input>")
			.appendTo(this.wrapper)
			.attr("title", "")
			.addClass("custom-multicombobox-input ui-widget ui-widget-content ui-state-default ui-corner-all")
			.keypress(function(e) {
    			var code = (e.keyCode ? e.keyCode : e.which);
    			if(code == 13) {
        			return false;
    			}
			})
			.autocomplete({
				delay: 0,
				minLength: 0,
				source: $.proxy(this, "_source")
			})
			.tooltip({
				tooltipClass: "ui-state-highlight"
			});

			this._on(this.input, {
				autocompletechange: "_checkItem",
				autocompleteselect: function(event, ui) {
					this.input.val(ui.item.value).blur();
					return false;
				},
			});
		},

		_createShowAllButton: function() {
			var input = this.input,
			wasOpen = false;

			$("<a>")
			.attr("tabIndex", -1)
			.attr("title", "Show All Items")
			.tooltip()
			.appendTo(this.wrapper)
			.button({
				icons: {
					primary: "ui-icon-triangle-1-s"
				},
				text: false
			})
			.removeClass("ui-corner-all")
			.addClass("custom-multicombobox-toggle ui-corner-right")
			.mousedown(function() {
				wasOpen = input.autocomplete("widget").is(":visible");
			})
			.click(function() {
				input.focus();

				// Close if already visible
				if (wasOpen) {
					return;
				}

				// Pass empty string as value to search for, displaying all results
				input.autocomplete("search", "");
			});
		},

		_createSelectedList: function() {
			var that = this;
			this.selected_list = $('<ul>')
			.addClass("custom-multicombobox-selected")
			.sortable({axis: "y"})
			.insertAfter(this.wrapper);
			$.each(this.items, function(i, index) {
				item = {
					label: that.list[index],
					value: that.list[index],
					index: index,
				};
				that._addItem(item);
			});
		},

		_source: function(request, response) {
			var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
			var selected = this.selected;
			response($.map(this.list, function(text, index) {
				if ((selected.indexOf(index) == -1) && (!request.term || matcher.test(text))) {
					return {
						label: text,
						value: text,
						index: index,
					};
				}
			}));
		},

		_checkItem: function(event, ui) {
			// Search for a match (case-insensitive)
			var item = {};
			var value = this.input.val(),
				valueLowerCase = value.toLowerCase(),
				valid = false;

			if (value == "") {
				return;
			}

			$.each(this.list, function(index, text) {
				if (text.toLowerCase() === valueLowerCase) {
					valid = true;
					item = {
						label: text,
						value: text,
						index: index,
					}
					return false;
				}
			});

			if (valid) {
				this._addItem(item);
				this.element.trigger("change");
			} else {
				this._displayMessage(value + " didn't match any item");
			}
		},

		_displayMessage: function(message) {
			this.input
			.val("")
			.attr("title", message)
			.tooltip("open");
			this._delay(function() {
				this.input.tooltip("close").attr("title", "");
			}, 2500);
			this.input.autocomplete("instance").term = "";
		},

		_addItem: function(item) {
			var selected = this.selected;

			if (selected.length >= this.limit) {
				var message = "You can't add more than " + this.limit + " item";
				if (this.limit > 1) {
					message += "s";
				}	
				this._displayMessage(message);
				return;
			}


			this.input.val("");
			this.input.autocomplete("instance").term = "";
			if (selected.indexOf(item.index) >= 0) {
				return;
			}
			if (!this.list[item.index]) {
				return;
			}
			var list_element = $('<li>')
			.css('cursor', "ns-resize")
			.html(" " + item.value);

			$('<span>')
			.button({
				icons: {
					primary: "ui-icon-close"
				},
				text: false
			})
			.click(function () {
				selected.splice(selected.indexOf(item.index), 1);
				list_element.remove();
			})
			.prependTo(list_element);

			$('<input>')
			.attr('name', this.input_name)
			.attr('type', "hidden")
			.attr('value', item.index)
			.appendTo(list_element);

			list_element.appendTo(this.selected_list);

			selected.push(item.index);
		},

		_destroy: function() {
			this.wrapper.remove();
			this.element.show();
		},
		
		values: function(values) {
			if (values) {
				this.items = values;
				this.selected = [];
				this.selected_list.remove();
				this._createSelectedList();
				this.element.trigger("change");
			}
			values = [];
			this.selected_list.find("input").each(function () {
				values.push($(this).val());
			})
			return values;
		}
	});
})(jQuery);
