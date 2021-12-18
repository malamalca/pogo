jQuery.fn.TplItemSortable = function(p_options)
{
	var default_options = {
		reorderUrl: "",
	};
	var options = jQuery().extend({}, default_options, p_options);
	
	var $this = this;
	var item_start_pos = null; // this is base position of an item before reorder
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// reorder items
	$(this).sortable({
		handle: "span.handle",
		start: function(event, ui) { item_start_pos = ui.item.index(); },
		stop: function(event, ui) { $this.reorderItems(ui.item); }
	}).disableSelection();
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// reorder main function
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
				$("span.handle", $this).slice(i, j+1).each(function() {
					$(this).html((i+1) + '.');
					i++;
				});
			}).error(function() {
				$this.sortable("cancel");
			});
		}
	}
}