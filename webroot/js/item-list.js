var editor = null;

jQuery.fn.ItemList = function(options)
{
	var default_options = {
        projectId: "",
		postUrl: "",
		reorderUrl: "",
		cloneUrl: "",
		itemUrl: "",
		newItemTemplate: "",
		checkFormulaUrl: "",
		addTplItemDialogCaption: "Add item from template",
		modifiedMessage: "Item has been modified. Exit without saving changes?",
		confirmDeleteMessage: "Are you sure you want to delete this item?",

		templateEditorUrl: "",
		templateEditorCaption: "Template elements",
		templateEditorWidth: 800,
		templateEditorHeight: 600,
		templates: null,
		templateMapUrl: ""
	};
	var options = jQuery().extend({}, default_options, options);

	var $this = this;

	var item_drag_mode = 'default'; // or "clone"
	var item_start_pos = null; // this is base position of an item before reorder

	////////////////////////////////////////////////////////////////////////////////////////////////
	// init editor
	editor = new ItemEditor({
		element:			"#view-section-edit-form",
		projectId:          options.projectId,
		postUrl:			options.postUrl,
		itemUrl:			options.itemUrl,
		checkFormulaUrl:	options.checkFormulaUrl,
		modifiedMessage:	options.modifiedMessage,

		templateEditorUrl: 	   options.templateEditorUrl,
		templateEditorCaption: options.templateEditorCaption,
		templateEditorWidth:   options.templateEditorWidth,
		templateEditorHeight:  options.templateEditorHeight,
		templateMapUrl:        options.templateMapUrl,
		templates:             options.templates,

		onShow: function() {
			$("#view-section-sortable").sortable("disable");
		},
		onHide: function() {
			$("#view-section-sortable").sortable("enable");
		},
		onUpdate: function(data, src_el) {
			$(".col-item-descript", src_el).html($this.nl2br(data.descript));

			if (data.unit == "m^1") data.unit = "m<sup>1</sup>";
			else if (data.unit == "m^2") data.unit = "m<sup>2</sup>";
			else if (data.unit == "m^3") data.unit = "m<sup>3</sup>";
			$(".col-item-unit", src_el).html(data.unit);

			var total_qty = null;
			if (typeof data.qty != "undefined") total_qty = data.qty;

			//if (typeof data.Qty != "undefined") {
			//	for (qty_idx in data.Qty) {
			//		if (data.Qty[qty_idx].qty_value) {
			//			total_qty += data.Qty[qty_idx].qty_value;
			//		}
			//	}
			//}
			$(".col-item-qty", src_el).html(total_qty ? LilFloatFormat(parseFloat(total_qty)) : "");
			$(".col-item-price", src_el).html(data.price ? LilFloatFormat(parseFloat(data.price)) : "");

            var total = "";
            var totalSum = Math.round(data.price * total_qty * 100) / 100
			if (total_qty && data.price) total = LilFloatFormat(totalSum, 2);
			$(".col-item-total", src_el).html(total);

			$this.calculateTotalSum();
		},
		onAdd: function(data, src_el) {
			var rx_id = new RegExp("__id__", "ig");
            var rx_order = new RegExp("__order__", "ig");

            var li = $(options.newItemTemplate.replace(rx_id, data.id).replace(rx_order, data.sort_order));
            console.log(options.newItemTemplate);

			$(".col-item-order span.handle", li).html(data.sort_order);
			$(".col-item-descript", li).html($this.nl2br(data.descript));
			$(".col-item-unit", li).html(data.unit);
			$(".col-item-price", li).html(data.price ? LilFloatFormat(parseFloat(data.price)) : "");

			var total_qty = null;
			if (typeof data.qty != "undefined") total_qty = data.qty;
			//if (typeof data.Qty != "undefined") {
			//	for (qty_idx in data.Qty) {
			//		if (data.Qty[qty_idx].qty_value) {
			//			total_qty += data.Qty[qty_idx].qty_value;
			//		}
			//	}
			//}

			$(".col-item-qty", li).html(total_qty ? LilFloatFormat(parseFloat(total_qty)) : "");

            var total = "";
            var totalSum = Math.round(data.price * total_qty * 100) / 100
			if (total_qty && data.price) total = LilFloatFormat(totalSum, 2);
			$(".col-item-total", li).html(total);

			$this.adjustItem(li);

            var nearest_li = $(src_el).closest("li", $this);

			if ($(nearest_li).length) {
				$(nearest_li).after(li);
			} else {
				$($this).prepend(li);
			}
			$this.calculateTotalSum();

			var i = parseInt(data.sort_order);
			$("td.col-item-order span.handle").slice(i).each(function() {
				$(this).html(i+1);
				i++;
			});
		}
	});

	this.strip_tags = function(html) {
		var tmp = document.createElement("DIV");
		tmp.innerHTML = html;
		return tmp.textContent || tmp.innerText;
	}
	this.nl2br = function(str, is_xhtml) {
		var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
		return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
	}
	this.calculateTotalSum = function() {
		var grand = 0;
		$("td.col-item-total", this).each(function() {
			grand += LilFloatStringToFloat($(this).html());
		});

		$("th.col-item-grand-total").html(LilFloatFormat(grand, 2));
	}

	////////////////////////////////////////////////////////////////////////////////////////////////
	// adjust events on single li
	this.adjustItem = function(li)
	{
		// aply editor to <li>s and add links
		$("a.view-section-add-item", li).click(function(e) {
            var li = $(this).closest("li");
			if (editor.show($(this).parent(),
				{
					sort_order: parseInt($("td.col-item-order span.handle", li).html()) + 1,
				})) {
				$(this).parent().hide();
			}
			return false;
		});
		$("a.view-section-add-tpl-item", li).click(function() {
			popup(options.addTplItemDialogCaption, $(this).attr("href"), 550, 650);
			return false;
		});
		$("table.view-section-item", li).click(function() {
            // edit clicked item
            var itemId = $(li).attr("id").substr(3);
            var rx_id = new RegExp("__id__", "ig");
            var currentLI = this;

            // fetch item data
            $.ajax({
                url: options.itemUrl.replace(rx_id, itemId),
                dataType: "json",
                success: function(data) {
                    // show item editor
                    if (editor.show(currentLI, data)) {
                        $(currentLI).hide();
                    }
                }
            });

			return false;
		});

		// delete items
        $(".col-item-delete", li)
            .on("mouseover", function() { $("a", this).show(); })
            .on("mouseout", function() { $("a", this).hide(); });

		$(".view-section-delete-item", li).click(function() {
			$this.deleteItem(this);
			return false;
        });

        $(".view-section-copy-item", li).click(function() {
            $this.copyItem(this);
            return false;
        });
	}

	////////////////////////////////////////////////////////////////////////////////////////////////
    // apply li functionality to the first link
	$("a#additm1").click(function(event) {
		if (editor.show($(this).parent(), { Item: { sort_order: 1 }})) {
			$(this).parent().hide();
		}
		return false;
	});
	$("a#addtplitm1").click(function(event) {
		popup(options.addTplItemDialogCaption, $(this).attr("href"), 550, 650);
		return false;
	});

	////////////////////////////////////////////////////////////////////////////////////////////////
	// apply li functionality to every item
	$("li", this).each(function() {
		$this.adjustItem(this);
	});

	////////////////////////////////////////////////////////////////////////////////////////////////
	// setup sortable for reordering items
    //$(this).disableSelection();

	$(this).sortable({
		cursor: "move",
		helper: "clone",
		handle: "span.handle",
		start: function(event, ui) {
			$(document).on("keydown", { ui: ui }, $this.handleItemDragCtrlDown);
			$(document).on("keyup", { ui: ui }, $this.handleItemDragCtrlUp);
			item_start_pos = ui.item.index();
		},
		stop: function(event, ui) {
			if (item_drag_mode == "clone") {
				$this.cloneItem(ui.item);
			} else {
				$this.reorderItems(ui.item);
			}

			$(document).off("keydown", $this.handleItemDragCtrlDown);
			$(document).off("keyup", $this.handleItemDragCtrlUp);
		},

	});

	this.handleItemDragCtrlDown = function(event)
	{
		if (event.keyCode == 17 && event.ctrlKey) {
			if (item_drag_mode != "clone") {
				$(event.data.ui.item).after($(event.data.ui.item).clone().addClass("drag-copy").show());
			}

			item_drag_mode = "clone";

		}
	}

	this.handleItemDragCtrlUp = function(event)
	{
		if (event.keyCode == 17 && !event.ctrlKey) {
			if (item_drag_mode == "clone") {
				$(event.data.ui.item).next().remove();
			}
			item_drag_mode = 'default';
		}
	}

	// reorder item main function
	this.reorderItems = function(item)
	{
		if (item_start_pos != item.index()) {
			var position = item.index();
			var item_id = $(item).attr("id").substr(3);

			var rx_item = new RegExp("(\\%5B){2}item_id(\\%5D){2}", "i");
			var rx_position = new RegExp("(\\%5B){2}position(\\%5D){2}", "i");

			var targetUrl = options.reorderUrl
				.replace(rx_item, item_id)
				.replace(rx_position, position + 1);

			// send new position
			$.get(targetUrl, function(data) {
				// update items below
				var i = position;
				var j = item_start_pos;
				if (item_start_pos < i) {
					j = i;
					i = item_start_pos;
				}
				$("td.col-item-order span.handle", $this).slice(i, j+1).each(function() {
					$(this).html(i+1);
					i++;
				});
			}).error(function() {
				$this.sortable("cancel");
			});
		}
	}

	// clone item main function
	this.cloneItem = function(item)
	{
		var position = item.index();
		var item_id = $(item).attr("id").substr(3);
		var rx_item = new RegExp("(\\%5B){2}item_id(\\%5D){2}", "i");
		var rx_position = new RegExp("(\\%5B){2}position(\\%5D){2}", "i");

		var targetUrl = options.cloneUrl
			.replace(rx_item, item_id)
			.replace(rx_position, position + 1);

		$("li", $this).eq(item_start_pos).removeClass("drag-copy");

		$.get(targetUrl, function(data) {
			// update items below
			var i = position;
			$("td.col-item-order span.handle", $this).slice(i).each(function() {
				$(this).html(i+1);
				i++;
			});

			$this.adjustItem(this);
			$this.calculateTotalSum();
		}).error(function() {
			$this.sortable("cancel");
			$("li", $this).eq(item_start_pos).remove();
			item_drag_mode = "default"; // reset
		});


	}

	// delete item main function
	this.deleteItem = function(element)
	{
		var li = $(element).closest("li");

		$(li).addClass("drag-copy");
		if (confirm(options.confirmDeleteMessage)) {
			$.get(
				$(element).attr("href"),
				function(data) {
					var sort_order = parseInt($("td.col-item-order span.handle", li).html()) + 1;

					var i = sort_order - 1;
					$("td.col-item-order span.handle").slice(i).each(function() {
						$(this).html(i);
						i++;
					});

					$(li).remove();

					$this.calculateTotalSum();
				}
			);
		}

		$(li).removeClass("drag-copy");
		return false;
    }
}
