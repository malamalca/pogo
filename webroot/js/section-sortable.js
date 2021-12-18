jQuery.fn.SectionSortable = function(p_options)
{
	var default_options = {
		reorderSectionUrl: "",
	};
	var options = jQuery().extend({}, default_options, p_options);

	var $this = this;
	var section_start_pos = null; // this is base position of an item before reorder

	////////////////////////////////////////////////////////////////////////////////////////////////
	// reorder items
	$(this).sortable({
		handle: "span.handle",
		start: function(event, ui) { section_start_pos = ui.item.index(); },
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
	// reorder Sections main function
	this.reorderSections = function(item)
	{
		if (section_start_pos != item.index()) {
			var position = item.index();
			var section_id = $(item).attr("id").substr(3);

			var rx_section = new RegExp("(\\%5B){2}section_id(\\%5D){2}", "i");
			var rx_position = new RegExp("(\\%5B){2}position(\\%5D){2}", "i");

			var targetUrl = options.reorderSectionUrl
				.replace(rx_section, section_id)
				.replace(rx_position, position + 1);

			// send new position
			$.get(targetUrl, function(data) {
				// update items below
				var i = position;
				var j = section_start_pos;
				if (section_start_pos < i) {
					j = i;
					i = section_start_pos;
				}
				$("span.handle", $this).slice(i, j+1).each(function() {
					$(this).html($this.romanize(i+1) + '.');
					i++;
				});
			}).error(function() {
				$this.sortable("cancel");
			});
		}
	}
}
