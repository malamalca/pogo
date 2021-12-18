jQuery.fn.CalculatedField = function(options)
{
	var default_options = {
		formulaField:    null,
		auxField:        null,
		noField:         null,
		lenField:        null,
		areaField:       null,
		projectId:       "",
		checkFormulaUrl: "",
		onNumbersChange: function() {}
	};
	var $this = this;

	this.onFormulaFieldFocus = function() {
		$(this).addClass('formula-field-focused');

		var options =  $(this).data('options');
        var newValue = $(options.formulaField).val();
		$(this).val(newValue);
	};
	this.onFormulaFieldBlur = function() {
		var options =  $(this).data('options');
		$(options.formulaField).val($(this).val());

		$(this).removeClass('formula-field-focused');
		$(this).removeClass("validation-error");
		$(this).attr('title', '');

		if ($(this).val().substr(0, 1) == '=') {
			$this.validateFormula(this, this);
		} else if (LilFloatIsValidFloat($(this).val())) {
			$(this).val(LilFloatFormat($(this).val(), 2));
			options.onNumbersChange();
		} else {
			if ($(this).val() != "") $(this).addClass("validation-error");
			options.onNumbersChange();
		}

	}

	this.update = function() {
		var options =  $(this).data('options');
		var formula = $(options.formulaField).val();
		if (formula.substr(0, 1) == '=') {
			$this.validateFormula(options.formulaField, $this);
		}
	}

	this.validateFormula = function(formulaField, targetField) {
		var options =  $(this).data('options');

		var field_formula = new Array({name: 'expression', value:$(formulaField).val()});
		field_formula.push({name:'project_id', value: options.projectId});
		if (options.auxField) field_formula.push({name:'aux', value: $(options.auxField).val()});
		if (options.noField) field_formula.push({name:'no', value: $(options.noField).val()});
		if (options.lenField) field_formula.push({name:'len', value: $(options.lenField).val()});
		if (options.areaField) field_formula.push({name:'area', value: $(options.areaField).val()});

		// disable field
		$(this).attr("disabled", true);
		// check formula
		$.ajax({
			type:     'POST',
			dataType: "json",
			//async:    false,
			url:      options.checkFormulaUrl,
			data:     field_formula,
			success:  function(data) {
				$(targetField).attr("disabled", false);
				if (data['result'] !== false) {
					$(targetField).val(LilFloatFormat(parseFloat(data['result'])));
					options.onNumbersChange();
				} else {
					$(targetField).addClass("validation-error");
					$(targetField).attr('title', data['error']);
				}
			},
			error: function() {
				$(targetField).attr("disabled", false);
				$(targetField).addClass("validation-error");
			}
		});

	}

	if (typeof options == "string") {
		if (options == "reload") {
			$this.update();
		}
	} else {
		$(this).data('options', jQuery().extend(true, {value: $(this).val()}, default_options, options));
		if ($(this).val()) {
			$(this).val(LilFloatFormat(parseFloat($(this).val())));
		}
		$(this).focus(this.onFormulaFieldFocus);
		$(this).blur(this.onFormulaFieldBlur);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
ItemEditor = function(p_options, p_anchor, p_data)
{
	var default_options = {
		anchor: null,
		original: null,
		item: {
			id:"", sort_order:1, descript:"", unit:"", price:null, qty:0,
			qties:  []
		},

		postUrl: "",
		checkFormulaUrl: "",
		modifiedMessage: "Item has been modified. Exit without saving changes?",

		projectId: "",

		onShow : function() {},
		onHide : function() {},
		onAdd : function() {},
		onUpdate: function() {}
	};

	var $this = this;
	var modified = false;
	var editor;
	var options;
    var qty_start_pos = null;
    var isQtyInLastRow = false;

	var	ItemId;
	var ItemSectionId;
	var	ItemDescript;
	var	ItemUnit;
	var	ItemPrice;
	var	ItemSortOrder;
	var	ItemOrder;
	var	ItemTotal;
	var ItemQty;

	var	FirstQtyRow;

	this.onNumbersChange = function(e) {
		let totalQty = 0;

        let qtyLineCount = $(".editor-qties>tbody>tr", editor).length;

        if (qtyLineCount > 1) {
            // there are many qty rows
            $("li.row-qty", editor).each(function() {
                if (LilFloatIsValidFloat($("input.qties-qty_value", this).val())) {
                    totalQty += LilFloatStringToFloat($("input.qties-qty_value", this).val());
                }
            });
            $(ItemQty).html(LilFloatFormat(totalQty, 2));
        } else {
            // single qty is moved to bottom row
            let singleQtyValue = $("input.qties-qty_value", ItemQty).val();
            if (LilFloatIsValidFloat(singleQtyValue)) {
                totalQty = LilFloatStringToFloat(singleQtyValue);
            }
        }


		if (ItemPrice.val()) {
			let price = ItemPrice.LilFloatVal();
            let total = Math.round(price * totalQty * 100) / 100;
			$(ItemTotal).html(LilFloatFormat(total, 2));
		} else {
			$(ItemTotal).html("");
		}
	};

	this.cloneQtyRow = function(e) {
        if (isQtyInLastRow) {
            $this.moveQtyFieldUp();
        }

		var newRow = $(FirstQtyRow).clone();
		$("td.row-qties ul", editor).append(newRow);

		$this.clearRow(newRow);
		$this.renumberRow(newRow, $("li.row-qty", editor).size());

		$("input.qties-sort_order", newRow).val($("li.row-qty", editor).size());

        $this.activateRow(newRow);
        $this.onNumbersChange();
	};

	this.activateRow = function(row) {
        let QtyValueField = $("input.qties-qty_value", row);
        let AuxValueField = $("input.qties-aux_value", row);

		AuxValueField.CalculatedField({
			formulaField:    $("input.qties-aux_formula", row),
			projectId:       options.projectId,
			checkFormulaUrl: options.checkFormulaUrl,
			onNumbersChange: function() {
				$(QtyValueField).CalculatedField("reload");
				$this.onNumbersChange
		 	}
		});

		QtyValueField.CalculatedField({
			formulaField:    $("input.qties-qty_formula", row),
			auxField:        $("input.qties-aux_value", row),
			projectId:       options.projectId,
			checkFormulaUrl: options.checkFormulaUrl,
			onNumbersChange: $this.onNumbersChange
		});

		$(".col-item-delete img", row).click($this.deleteRow).hide();
		$(".col-item-delete", row)
			.mouseover(function() { $("img", this).show(); })
			.mouseout(function() { $("img", this).hide(); });
    }

	this.deleteRow = function() {
		var row = $(this).closest("li");
		var id = $("input.qties-id", row);
		if (id) {
			// add this item do deleted items list
			var del_list = $('<input/>')
				.attr({
					type: 'hidden',
					name: 'qties_to_delete[]'
				})
				.val($(id).val());
			$(del_list).insertAfter('#ItemId');
		}

        let qtyCount = $("li.row-qty", editor).size();
		if (qtyCount > 1) {
			var i = $("li.row-qty", editor).index(row);
			$("li.row-qty", editor).slice(i).each(function() {
				$("span.qty-handle", this).html(i);
				$(".qties-sort_order", this).val(i);
				i++;
			});
            $(row).remove();

            if (qtyCount == 2) {
                $this.moveQtyFieldDown();
            }
		} else {
            $this.clearRow(row, 1);
		}

		$this.onNumbersChange();
    }

	this.renumberRow = function(row, order) {
		$("input.qties-id", row).attr("name", "qties["+order+"][id]");
		$("input.qties-item_id", row).attr("name", "qties["+order+"][item_id]");
		$("input.qties-sort_order", row).attr("name", "qties["+order+"][sort_order]");
		$("input.qties-descript", row).attr("name", "qties["+order+"][descript]");

		$("input.qties-aux_formula", row).attr("name", "qties["+order+"][aux_formula]");
		$("input.qties-aux_value", row).attr("name", "qties["+order+"][aux_value]");
		$("input.qties-qty_formula", row).attr("name", "qties["+order+"][qty_formula]");
		$("input.qties-qty_value", row).attr("name", "qties["+order+"][qty_value]");

		$(".col-item-order span.qty-handle", row).html(order);
    }

	this.clearRow = function(row, order) {
		$("input.qties-id", row).val("");
		$("input.qties-item_id", row).val($(ItemId).val());
		$("input.qties-sort_order", row).val("1");
		$("input.qties-descript", row).val("");

		$("input.qties-aux_formula", row).val("");
		$("input.qties-aux_value", row).val("");
		$("input.qties-qty_formula", row).val("");
		$("input.qties-qty_value", row).val("");

		$("input", row).removeClass("validation-error")

		if (!order) order = 1;
		$(".col-item-order span.qty-handle", row).html(order);
	}

	this.updateData = function() {
        options.original = options.item;

		$(ItemId).val(options.item.id);
		$(ItemDescript).val(options.item.descript);
        $(ItemUnit).val(options.item.unit);
		$(ItemPrice).val(options.item.price !== null ? LilFloatFormat(parseFloat(options.item.price)) : "");
		$(ItemSortOrder).val(options.item.sort_order);
		$(ItemOrder).html(options.item.sort_order);

		if (typeof options.item.qties != 'undefined') {
			var row = FirstQtyRow;
			var qty = null;

            $this.clearRow(row, 1);

			if (options.item.qties.length > 0) {
                let i = 1;
				for (qty_index in options.item.qties) {
                    qty = options.item.qties[qty_index];
					if (i > 1) {
						// duplicate row, insert it and fill it with data
						row = $(FirstQtyRow).clone();
						$("td.row-qties ul", editor).append(row);
						$this.renumberRow(row, $("li.row-qty", editor).size());
						$this.activateRow(row);
					}
					$("input.qties-id", row).val(qty.id);
					$("input.qties-item_id", row).val(qty.item_id);
					$("input.qties-sort_order", row).val(qty.sort_order);
					$("input.qties-descript", row).val(qty.descript ? qty.descript : "");

					$("input.qties-aux_formula", row).val(qty.i18n_aux_formula);
					$("input.qties-aux_value", row).val(qty.aux_value ? LilFloatFormat(parseFloat(qty.aux_value)) : "");

					$("input.qties-qty_formula", row).val(qty.i18n_qty_formula);
					$("input.qties-qty_value", row).val(qty.qty_value ? LilFloatFormat(parseFloat(qty.qty_value)) : "");

					$(".col-item-order span.qty-handle", row).html(qty.sort_order);

					i++;
                }
			}
		}

        $this.moveQtyFieldDown();
		$this.onNumbersChange();


		modified = false;
    }
    /**
     * If there is only single qty line, set adjust qty field position to be in line with price and unit
     */
    this.moveQtyFieldDown = function() {
        let qtyLineCount = $(".editor-qties>tbody>tr", editor).length;
        if (qtyLineCount == 1) {
            // get all elements from qty cell
            let qtyFields = $(".editor-qties>tbody>tr:first>td.col-item-qty", editor).children();

            // clear total qty cell and append qty fields
            $(ItemQty).html("").append(qtyFields);

            isQtyInLastRow = true;
        }
    }
    /**
     * If there are more qty line, adjust qty field position to be in line with price and unit
     */
    this.moveQtyFieldUp = function() {
        if (isQtyInLastRow) {
            // get all elements from qty cell
            let qtyFields = $(ItemQty).children();

            // clear total qty cell and append qty fields
            $(".editor-qties>tbody>tr:first>td.col-item-qty", editor).append(qtyFields);
            $(ItemQty).html("");

            isQtyInLastRow = false;
        }
    }
	this.updateModified = function() {
		if (!options.anchor || (typeof options.original == "undefined") || !options.original) {
			modified = false;
			return;
        }

		modified =
			($(ItemDescript).val() != options.original.descript) ||
			($(ItemUnit).val() != options.original.unit) ||
			(options.original.price !== null && ($(ItemPrice).LilFloatFormat() != LilFloatFormat(parseFloat(options.original.price))));
	}
	this.removeEditor = function() {
		if (options.anchor && modified && !confirm(options.modifiedMessage)) {
			return false;
		}

		$(options.anchor).show();
        $(editor).detach();

        // reset qty position
        if (isQtyInLastRow) {
            $this.moveQtyFieldUp();
        }

		// remove qtys
		$("li.row-qty:not(:first)", editor).remove();
		$this.clearRow(FirstQtyRow);

		// remove validation errors
		$("input.validation-error", editor).removeClass("validation-error");

		// on hide callback
		options.onHide.apply(editor);

		// remove esc handler
		$(document).off('keyup.item-editor');
		$(".col-item-submit input", editor).attr("disabled", false);

        options.anchor = null;

		return true;
	}
	this.sendData = function(form) {
		$(".col-item-submit input", editor).attr("disabled", true);

		var rx_id = new RegExp("__id__", "ig");

		$.post(
			options.postUrl.replace(rx_id, $(ItemId).val()),
			$(form).serialize(),
			function(data) {
				var anchor = options.anchor;

				if ((typeof data.result == 'undefined') || !data.result) {
                    // error
					for (var field in data.errors) {
						// check if error is from base class (Item)
						if (data.errors[field] instanceof Array) {
                            // qties
                            for (var lineNo in data.errors[field]) {
                                for (var subField in data.errors[field][lineNo]) {
                                    if (data.errors[field][lineNo][subField] instanceof Object) {
                                        for (var errorName in data.errors[field][lineNo][subField]) {
                                            $("[name='"+field+"["+(parseInt(lineNo)+1)+"]["+subField+"]']", editor).addClass("validation-error");
                                        }
                                    }
                                }
                            }
							//$("[name='data["+model+"]["+field+"]']", editor).addClass("validation-error");
						} if (data.errors[field] instanceof Object) {
							for (var errorName in data.errors[field]) {
								$("[name='"+field+"']", editor).addClass("validation-error");
							}
						}
					}
				} else {
					modified = false;
					$this.removeEditor();
					if (options.item.id) {
						options.onUpdate.apply(editor, [data.data, anchor]);
					} else {
						options.onAdd.apply(editor, [data.data, anchor]);
					}
				}
				return false;
			},
			"json"
		);
		$(".col-item-submit input", editor).attr("disabled", false);
		return false;
	}
	this.show = function(p_anchor, p_data) {
		this.updateModified();
		if (this.removeEditor()) {
			options.anchor = p_anchor;
            options.item = jQuery().extend(true, {}, default_options.item, p_data);

			this.updateData();

			$(editor).insertAfter(options.anchor).show();
			options.onShow.apply(editor);

			// cancel editing with esc
			$(document).on('keyup.item-editor', function(e) {
				if ((e.keyCode == 27)) {
					e.preventDefault();
                    $this.updateModified();
                    return $this.removeEditor();
				}
			});

			ItemDescript.focus();

			return true;
		}
		return false;
	}
	this.reorderQties = function(item)
	{
		if (qty_start_pos != item.index()) {
			var position = item.index() + 1;
			var i = position - 1;
			if (qty_start_pos < i) i = qty_start_pos;
			$(".row-qty", editor).slice(i).each(function() {
				$("span.qty-handle", this).html(i+1);
				$(".qties-sort_order", this).val(i+1);
				i++;
			});
		}
	}

	// initialization
	options = jQuery().extend(true, {}, default_options, p_options);
	editor = $(options.element);

	var LastRow = $("tr#row-calculation", editor);

	ItemId        = $('#ItemId', editor);
	ItemSectionId = $('#ItemSectionId', editor);
	ItemDescript  = $('#ItemDescript', editor);
	ItemUnit      = $('#ItemUnit', editor);
	ItemPrice     = $('#ItemPrice', editor);
	ItemSortOrder = $('#ItemSortOrder', editor);
	ItemOrder     = $(".col-item-order span.handle", editor);
	ItemTotal     = $(".col-item-total", LastRow);
	ItemQty       = $(".col-item-qty", LastRow);

    FirstQtyRow = $('li.row-qty', editor);
	$this.activateRow(FirstQtyRow);

	$(ItemPrice).blur(function() { $this.onNumbersChange(); });
    $(ItemDescript).autogrow();
    $(ItemPrice).LilFloat({places:2, empty: true});

	$("td.row-qties ul", editor).sortable({
		handle: "span.qty-handle",
		start: function(event, ui) { qty_start_pos = ui.item.index(); },
		stop: function(event, ui) { $this.reorderQties(ui.item); }
	})

	$("#ItemEditForm", editor).submit(function() { return $this.sendData(this); });
	$("#view-section-cancel", editor).click(function() { $this.updateModified(); return $this.removeEditor(); });
	$("#view-section-add-qty", editor).click(function() { $this.cloneQtyRow(); });
}
