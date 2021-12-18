jQuery.fn.CategorySortable = function(p_options)
{
	var default_options = {
		reorderCategoryUrl: "",
	};
	var options = jQuery().extend({}, default_options, p_options);

	var $this = this;
	var category_start_pos = null; // this is base position of an item before reorder
	var section_start_pos = null;
	var section_start_category = null;
	var section_start_category_id = null;

	////////////////////////////////////////////////////////////////////////////////////////////////
	// reorder items
	$(this).sortable({
		handle: "span.handle",
		start: function(event, ui) { category_start_pos = ui.item.index(); },
		stop: function(event, ui) { $this.reorderCategories(ui.item); }
	});

	$(".dashboard-sections", this).sortable({
		connectWith: $(".dashboard-sections", this),
		handle: "span.sec_handle",
		start: function(event, ui) {
			section_start_pos = ui.item.index();
			section_start_category = $(ui.item).closest("li.dashboard-category");
			section_start_category_id = $(section_start_category).attr("id").substr(3)
		},
		stop: function(event, ui) { $this.reorderSections(ui.item); }
	});

	this.romanize = function(num) {
		var lookup = {M:1000,CM:900,D:500,CD:400,C:100,XC:90,L:50,XL:40,X:10,IX:9,V:5,IV:4,I:1},
			roman = '',
			i;
		for ( i in lookup ) {
			while ( num >= lookup[i] ) {
				roman += i;
				num -= lookup[i];
			}
		}
		return roman;
	}

	////////////////////////////////////////////////////////////////////////////////////////////////
	// reorder Categories main function
	this.reorderCategories = function(item)
	{
		if (category_start_pos != item.index()) {
			var position = item.index();
			var category_id = $(item).attr("id").substr(3);

			var rx_category = new RegExp("(\\%5B){2}category_id(\\%5D){2}", "i");
			var rx_position = new RegExp("(\\%5B){2}position(\\%5D){2}", "i");

			var targetUrl = options.reorderCategoryUrl
				.replace(rx_category, category_id)
				.replace(rx_position, position + 1);

			// send new position
			$.get(targetUrl, function(data) {
				// update items below
				var i = position;
				var j = category_start_pos;
				if (category_start_pos < i) {
					j = i;
					i = category_start_pos;
				}
				$("span.cat_handle", $this).slice(i, j+1).each(function() {
					$(this).html(String.fromCharCode(64+i+1) + '.');
					i++;
				});
			}).error(function() {
				$this.sortable("cancel");
			});
		}
	}
	////////////////////////////////////////////////////////////////////////////////////////////////
	// reorder Sections main function
	this.reorderSections = function(item)
	{
		var target_category = $(item).closest("li.dashboard-category");
		var category_id     = $(target_category).attr("id").substr(3);
		var section_id      = $(item).attr("id").substr(3);
		var position        = item.index();

		if ((section_start_pos != position) || (category_id != section_start_category_id)) {
			var rx_category = new RegExp("(\\%5B){2}category_id(\\%5D){2}", "i");
			var rx_section  = new RegExp("(\\%5B){2}section_id(\\%5D){2}", "i");
			var rx_position = new RegExp("(\\%5B){2}position(\\%5D){2}", "i");

			var targetUrl = options.reorderSectionUrl
				.replace(rx_category, category_id)
				.replace(rx_section, section_id)
				.replace(rx_position, position + 1);

			// send new position
			$.get(targetUrl, function(data) {
				// update items below
				var i = position;
				var j = section_start_pos;

				if (category_id != section_start_category_id) {
                    // update target
					$("span.sec_handle", target_category).slice(i).each(function() {
						$(this).html($this.romanize(i+1) + '.');
						i++;
					});
					i = section_start_pos;
                    j = $("li", section_start_category).size()-1;

                    // update total sum
                    $("#cat" + data.previous.id + " .category-total").html(LilFloatFormat(data.previous.total));
                    $("#cat" + data.new.id + " .category-total").html(LilFloatFormat(data.new.total));
				}

				// update source menu
				if (j < i) {
					j = i;
					i = section_start_pos;
				}
				$("span.sec_handle", section_start_category).slice(i, j+1).each(function() {
					$(this).html($this.romanize(i+1) + '.');
					i++;
				});
			}).error(function() {
				$("ul", section_start_category).sortable("cancel");
			});
		}
	}
}
