var editor = null;
jQuery.fn.ItemReadonyList = function(options)
{
	var default_options = {
		captionShow: "Show",
		popupTitleShapes: "Shapes",
		itemUrl: "",
		onTemplateItemUrl: "",
		onTemplateQtyUrl: ""
	};
	var options = jQuery().extend({}, default_options, options);
	
	var $this = this;
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// adjust events on single li
	this.adjustItem = function(li)
	{
		$("table.view-section-item", li).click(function() {
			var id = $(this).parent('li').prop('id').substr(3);
			var table = $(this);
			var tbody = $(this).find('tbody')
			
			if ($(table).data('expanded') === true) {
				$("tr.qty", tbody).remove();
				$(table).data('expanded', null);
			} else {
				var rx_id = new RegExp("__id__", "ig");
				$.ajax({
					url: options.itemUrl.replace(rx_id, id),
					dataType: "json",
					async:false,
					success: function(data) {
						$(table).data('expanded', true);
						if (data.Qty.length > 1 || (data.Qty.length == 1 && data.Qty[0].descript.trim() != '')) {
							var qty = null;
							var rx_qty_id = new RegExp("__qty_id__", "ig");
							var rx_item_id = new RegExp("__item_id__", "ig");
							var previewTd = $('<td>').prop('class', 'col-item-price right');
							for (qty_index in data.Qty) {
								qty = data.Qty[qty_index];
								
								if (qty.shapes != "null") {
									previewTd = $('<td>')
										.prop('class', 'col-item-price right')
										.html($('<a></a>')
											.attr("href", options.onTemplateQtyUrl
												.replace(rx_qty_id, qty.id)
												.replace(rx_item_id, qty.item_id)
											)
            								.html(options.captionShow)
            								.click(function(e) { 
												e.preventDefault(); 
												popup(options.popupTitleShapes, $(this).prop('href'), 550, 650);
												return false;
											})
										);
								} else previewTd = $('<td>').prop('class', 'col-item-price right');
										
								$(tbody).append($('<tr>').prop('class', 'qty')
									.append($('<td>').text(''))
									.append($('<td>').text(''))
									.append($('<td>').prop('class', 'col-item-descript').text(qty.descript))
									.append($('<td>')
										.prop('class', 'col-item-unit center')
										.prop('title', qty.aux_formula)
										.text(LilFloatFormat(qty.aux_value)))
									.append($('<td>')
										.prop('class', 'col-item-qty right')
										.prop('title', qty.qty_formula)
										.text(LilFloatFormat(qty.qty_value)))
									.append(previewTd)
								);
							}
						}
					}
				});
			}
			
			return false;
		});
		
		$(".col-item-delete", li).hover(function() { $("a", this).toggle(); });
		$(".view-section-delete-item", li).click(function() { 
			popup(options.popupTitleShapes, $(this).prop('href'), 550, 650);
			return false;
		}).hide();
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// apply li functionality to every item	
	$("li", this).each(function() {
		$this.adjustItem(this);
	});
}