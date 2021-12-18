// http://jsfiddle.net/4xzDC/
ItemTemplateEditor = function(p_options)
{
	var default_options = {
		element: null,	// id of map element
		mapUrl: '',		// url for map tiles
		templates: [],  // array of available templates
		items: [],		// array of available items
		
		checkFormulaUrl: '',
		sectionId: '',
		
		onShow : function() {},
		onHide : function() {},
		onAdd : function() {},
		onUpdate: function() {},
		
		onShowTemplate: function(id) {}
	};
	
	var $this = this;
	var map = null;
	var options = [];
	var items = []; // array of items with reference to layer
	
	var tilesLayer = null;
	var objectLayerGroup = null;
	
	var currentTemplate = null;
	
	var justStoppedEditing = false;
	var editingInProgress = false;
	var openedPopup = false;
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// return data
	this.getData = function() {
		var ret = {
			'items': [],
			'no': 0,
			'len': 0,
			'area': 0
		};
		
		if (items) for (var i = 0; i < items.length; i++) {
			ret.items[i] = {
				template_id: items[i].template_id,
				kind:        items[i].kind,
				points:      items[i].points,
				formula:     items[i].formula,
				value:       items[i].value
			};
			
			switch (items[i].kind) {
				case "marker":
					ret.no += 1;
					break;
				case "polyline":
					ret.len += ((typeof items[i].value == 'number') ? items[i].value : LilFloatStringToFloat(items[i].value));
					break;
				case "polygon":
					ret.area += ((typeof items[i].value == 'number') ? items[i].value : LilFloatStringToFloat(items[i].value));
					break;
			}
		}
		return ret;
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// show template on map; template can be string (id) or object (template)
	this.showTemplate = function(template) {
		var rx_id = new RegExp("__id__", "ig");
		
		// if template is given by id, then we must find suitable template object
		if (typeof template == "string") {
			i = 0;
			while (i < options.templates.length) {
				if (options.templates[i].id == template) break;
				i++;
			}
			if ((options.templates.length == 0) || (options.templates[i].id != template)) {
				currentTemplate = null;
				return false;
			}
			template = options.templates[i];
		}
		
		currentTemplate = template;
		
		// cleanup existing tile layer and elements
		objectLayerGroup.clearLayers();
		if (tilesLayer != null) map.removeLayer(tilesLayer);
		
		// setup new layers
		//var boundsBottomLeft = map.unproject([0, template.h], map.getMaxZoom());
		//var boundsTopRight = map.unproject([template.w, 0], map.getMaxZoom());
		//var bounds = new L.LatLngBounds(boundsBottomLeft, boundsTopRight);
		
		tilesLayer = L.tileLayer(options.mapUrl.replace(rx_id, template.id), {
			noWrap:  true,
			minZoom: template.minZoom,
			//bounds: bounds
		}).addTo(map);
		
		var mapCenterX = Math.round(template.w / 2);
		var mapCenterY = Math.round(template.h / 2);
		var center = map.unproject([mapCenterX, mapCenterY], map.getMaxZoom());
		map.setView(center, template.minZoom < 10 ? template.minZoom : 10, {reset: true});
		
		// add items
		if (items) for (var i = 0; i < items.length; i++) {
			items[i].layer = null;
			if (items[i].template_id == template.id) {
				switch (items[i].kind) {
					case "marker":
						items[i].layer = L.marker(map.unproject([items[i].points[0][0], items[i].points[0][1]], map.getMaxZoom()));
						objectLayerGroup.addLayer(items[i].layer);
						break;
					case "polyline":
						var polyPoints = [];
						for (var pnt = 0; pnt < items[i].points.length; pnt++) {
							polyPoints[pnt] = map.unproject([items[i].points[pnt][0], items[i].points[pnt][1]], map.getMaxZoom());
						}
						items[i].layer = L.polyline(polyPoints).on("click", $this.onShapeClick);
						objectLayerGroup.addLayer(items[i].layer);
						break;
					case "polygon":
						var polyPoints = [];
						for (var pnt = 0; pnt < items[i].points.length; pnt++) {
							polyPoints[pnt] = map.unproject([items[i].points[pnt][0], items[i].points[pnt][1]], map.getMaxZoom());
						}
						items[i].layer = L.polygon(polyPoints).on("click", $this.onShapeClick);
						objectLayerGroup.addLayer(items[i].layer);
						break;
				}
			}
		}
		
		options.onShowTemplate.apply(this, [currentTemplate.id]);
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// event handler 
	// prompt for polyline length or polygon area on layer click 
	this.onShapeClick = function(e) {
		if (editingInProgress) return false;
		var layer = this;
		if (items) for (var i = 0; i < items.length; i++) {
			if (items[i].layer == layer) {
				$this.popupItemData(e.latlng, layer);
				break;
			}
		}
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// open popup with item data
	this.popupItemData = function(latlng, layer) {
		openedPopup = true;
		
		map.openPopup(
			L.popup()
				.setLatLng(latlng)
				.setContent(
					'<div id="TemplateEditorPopup">'+
					'<form id="PopupFormulaForm">'+
					'<label for="PopupFormula">Specify length or area:</label>'+
					'<input id="PopupFormula" value="" />'+
					'<br />'+
					'<button type="submit" id="PopupConfirm" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only">'+
					'<span>Save</span>'+
					'</button>'+
					'</form>'+
					'</div>')
		);
		
		if (items) for (var i = 0; i < items.length; i++) {
			if (items[i].layer == layer) {
				$("#PopupFormula").val(LilFloatFormat(items[i].value, 2));
				$("#PopupFormula").data("formula", items[i].formula);
				break;
			}
		}
		
		if (!items[i].formula) {
			$("#PopupFormula").focus();  // focus only when empty formula
			$("#PopupFormula").select();
		}
		
		$("#PopupFormula").focus($this.focusItemData);
		$("#PopupFormula").blur($this.blurItemData);
		
		//$("#PopupConfirm").click($this.saveAndClosePopup);
		$("#PopupFormulaForm").submit($this.saveAndClosePopup);
		$("#TemplateEditorPopup").data("layer", layer);
		
		// close popup on ESC
		$(document).one('keyup.formula-popup', function(e) {
			if ((e.keyCode == 27)) {
				openedPopup = false;
				map.closePopup();
			}
		});
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// focus poopup field
	this.focusItemData = function() 
	{
		$(this).val($(this).data("formula"));
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// blur popup field
	this.blurItemData = function() 
	{
		$(this).data("formula", $(this).val());
		
		$(this).removeClass("validation-error");
		$($this).attr("title", "");
		
		if ($(this).val().substr(0, 1) == '=') {
			var field_formula = new Array({name: 'data[expression]', value:$(this).val()});
			field_formula.push({name:'data[section_id]', value: options.sectionId});
			
			var $this = this;
			
			// disable field
			$(this).attr("disabled", true);
			
			// check formula
			$.ajax({
				type:     'POST',
				dataType: "json",
				async:    false,
				url:      options.checkFormulaUrl,
				data:     field_formula,
				success:  function(data) {
					$($this).attr("disabled", false);
					if (data['result'] !== false) {
						$($this).val(LilFloatFormat(parseFloat(data['result'])));
					} else {
						$($this).addClass("validation-error");
						$($this).attr('title', data['error']);
					}
				},
				error: function() {
					$($this).attr("disabled", false);
					$($this).addClass("validation-error");
				}
			});
		} else if (LilFloatIsValidFloat($(this).val())) {
			$(this).val(LilFloatFormat($(this).val(), 2));
		} else {
			if ($(this).val() != "") $(this).addClass("validation-error");
		}
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// save formula from popup
	this.saveAndClosePopup = function(e) 
	{
		e.preventDefault();
		
		var form = $(this).parent("#TemplateEditorPopup");
		var layer = $(form).data("layer");
		
		if ($("#PopupFormula", $(form)).is(":focus")) {
			$("#PopupFormula", $(form)).blur();
		}
		
		if ($("#PopupFormula", $(form)).hasClass("validation-error")) return false;
		
		if (form && layer) {
			if (items) for (var i = 0; i < items.length; i++) {
				if (items[i].layer == layer) {
					items[i].value = 0;
					
					if (LilFloatIsValidFloat($("#PopupFormula", form).val())) {
						items[i].value = LilFloatFormat($("#PopupFormula", form).val(), 2);
					}
					
					items[i].formula = $("#PopupFormula", form).data("formula");
					break;
				}
			}
		}
		map.closePopup();
		openedPopup = false;
	
		return false;
	}
	
	this.canClose = function()
	{
		var ret = true;
		
		if (openedPopup === true) ret = false;
		
		if (justStoppedEditing === true) ret = false;
		justStoppedEditing = false;

		return ret;
	}

	////////////////////////////////////////////////////////////////////////////////////////////////
	// initialization
	options = jQuery().extend(true, {}, default_options, p_options);
	items = options.items;
	
	map = L.map(options.element, {
		maxZoom: 10,
//		crs: L.CRS.Simple
	});
	
	var objectLayerGroup =  L.featureGroup().addTo(map);
	
	if (options.templates.length > 0) {
		var firstTemplateWithItems = 0;
		if (items) for (var i = 0; i < items.length; i++) {
			if (items[i].template_id) {
				for (var k = 0; k < templates.length; k++) {
					if (templates[k].id == items[i].template_id) {
						firstTemplateWithItems = k;
					}
				}
				break;
			}
			if (firstTemplateWithItems) break;
		}
		this.showTemplate(options.templates[firstTemplateWithItems]);
	}
	
	// Initialize the draw control and pass it the FeatureGroup of editable layers
	var drawControl = new L.Control.Draw({
		draw: {
			circle: false, // Turns off this drawing tool
			rectangle: false,
			polyline: {
				showLength: false
			},
			polygon: {
				showArea: false
			}
		},
	    edit: {
	        featureGroup: objectLayerGroup
	    }
	});
	map.addControl(drawControl);
	
	map.on('draw:deleted', function (e) {
		var layers = e.layers;
	    layers.eachLayer(function (layer) {
	    	if (items) for (var i = 0; i < items.length; i++) {
				if (items[i].layer == layer) {
					items.splice(i, 1);
					break;
				}
			}
	    });
	});
	
	map.on('draw:created', function (e) {
		var layer = e.layer;
		
		var Point = null;
		var points = [];
		var latsLngs = [];
		
		// get layer's lat and long
		if (e.layerType == 'marker') {
			latsLngs[0] = layer.getLatLng();
		} else {
			latsLngs = layer.getLatLngs();
			layer.on("click", $this.onShapeClick);
		}
		
		// convert lat and long to points
		for (var i = 0; i < latsLngs.length; i++) {
			Point = map.project(latsLngs[i], map.getMaxZoom());
			points[i] = [Point.x, Point.y]
		}
		
		// add new item
		if (!items) items = new Array();
		items[items.length] = {
			template_id: currentTemplate.id,
			kind:        e.layerType,
			points:      points,
			layer:       layer,
			formula:     "",
			value:       ""
		};
		objectLayerGroup.addLayer(layer);
		
		// popup
		if (e.layerType != 'marker') {
			$this.popupItemData(latsLngs[latsLngs.length-1], layer);
		}
	});
	
	map.on('draw:editstart', function (e) {
		editingInProgress = true;
	});
	map.on('draw:drawstop', function (e) {
		justStoppedEditing = true;
	});
	map.on('draw:editstop', function (e) {
		justStoppedEditing = true;
		editingInProgress = false;
	});
	
	
	map.on('draw:edited', function (e) {
		var layers = e.layers;
		var Point = null;
		var points = null;
		var latsLngs = null;
		
		layers.eachLayer(function (layer) {
			points = [];
			latsLngs = [];
			
			// get layer's lat and long
			if (typeof layer.getLatLng === 'function') {
				// it's a marker
				latsLngs[0] = layer.getLatLng();
			} else {
				latsLngs = layer.getLatLngs();
			}
			
			// convert lat and long to points
			for (var i = 0; i < latsLngs.length; i++) {
				Point = map.project(latsLngs[i], map.getMaxZoom());
				points[i] = [Point.x, Point.y]
			}
			
			// inject into items
			if (items) for (var i = 0; i < items.length; i++) {
				if (items[i].layer == layer) {
					items[i].points = points;
					break;
				}
			}
		});
		
	});
}