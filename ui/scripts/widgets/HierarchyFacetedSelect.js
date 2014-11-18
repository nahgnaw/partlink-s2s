/**
 * Create Namespace
 */
if (window.edu == undefined || typeof(edu) != "object") edu = {};
if (edu.rpi == undefined || typeof(edu.rpi) != "object") edu.rpi = {};
if (edu.rpi.tw == undefined || typeof(edu.rpi.tw) != "object") edu.rpi.tw = {};
if (edu.rpi.tw.sesf == undefined || typeof(edu.rpi.tw.sesf) != "object") edu.rpi.tw.sesf = {};
if (edu.rpi.tw.sesf.s2s == undefined || typeof(edu.rpi.tw.sesf.s2s) != "object") edu.rpi.tw.sesf.s2s = {};
if (edu.rpi.tw.sesf.s2s.widgets == undefined || typeof(edu.rpi.tw.sesf.s2s.widgets) != "object") edu.rpi.tw.sesf.s2s.widgets = {};

/**
 * Construct from Widget object
 */
edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect = function(panel) 
{
	var input = panel.getInput();
    	this.panel = panel;
    	var freetext = jQuery("<input type=\"text\"></input>");
    	jQuery(freetext).autocomplete({source: edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.autocompleteSource, select: edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.autocompleteSelect });
    	this.selectbox = jQuery("<div style=\"height:200px;overflow:auto;border:solid 1px;border-color:#A9A9A9\" class=\"data-selector\"><span>Loading...</span></div>");
   	this.div = jQuery("<div class=\"facet-content\" style=\"width:100%\"></div>");
	var self = this;
	panel.setInputData(input.getId(), function() {
		var arr = [];
	    	self.selectbox.find("input.x-selected").each(function() {
			arr.push(jQuery(this).val());
	    	});
		return arr;
	});
    	this.div.append(freetext);
    	this.div.append("<br/>");
    	this.div.append(this.selectbox);
}

edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.prototype.get = function()
{
	return this.div;
}

edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.prototype.reset = function()
{
    	this.selectbox.children().remove();
   	this.selectbox.append("<span>Loading...</span>");
}

edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.prototype.update = function(data)
{
	data = JSON.parse(data);
	data.sort(function(o1, o2) {
            return (o1.label <= o2.label) ? -1 : 1;
        });
	this.selectbox.children().remove();
	var tree = edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.buildTree(data, "http://xsb.com/swiss/product#PART");
	jQuery(tree).css("margin-left","0px");
    	this.selectbox.append(tree);
	var self = this;
	this.selectbox.find("span").click(function() {
		self.updateClick(this);
        });
	this.selectbox.find("img.trigger").click(edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.expandCollapse);
	this.selectbox.find("img.info").click(edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.showDialog);
}

edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.prototype.updateClick = function(elem)
{
    	var li = jQuery(elem).parents("li")[0];
    	if (jQuery(jQuery(li).find("input")[0]).hasClass("x-selected")) {
    		jQuery(jQuery(li).find("input")[0]).removeClass("x-selected");
		var padding = jQuery(li).css("padding-left");
        	jQuery(li).attr("style","background:inherit;border:none");	
		jQuery(li).css("padding-left",padding);
    	}
    	else {
        	jQuery(jQuery(li).find("input")[0]).addClass("x-selected");
		var padding = jQuery(li).css("padding-left");
		jQuery(li).attr("style","background:#C7DFFC;border:1px solid white;");
		jQuery(li).css("padding-left",padding);
    	}
    	this.panel.notify();
}

edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.autocompleteSource = function(term, callback)
{
    	var t = term;
    	var input = this.element;
    	var parent = jQuery(input).parent();
    	var options = jQuery(input).parent().find("li");
    	var data = [];
    	for (var i = 0; i < options.length; ++i) {
	    	var o = jQuery(options[i]);
	    	if (jQuery(o).find("span").html().toLowerCase().indexOf(term.term.toLowerCase()) > -1) {
			var j = 0;
		    	for ( ; j < data.length; ++j) {
				if (data[j]["myval"] == jQuery(o).find("input").val()) {
			    		break;
			 	}
		    	}
		    	if ( j == data.length ) {
				data.push({"label":jQuery(o).find("span").html(),"myval":jQuery(o).find("input").val(),"value":"","option":jQuery(o).find("span")[0]});
		    	}
		}
	}
    	callback(data);
}

edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.autocompleteSelect = function(event, ui)
{
    	var item = ui.item;
    	jQuery(item.option).click();
    	event.target.value = '';
    	event.stopPropagation();
}

edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.buildTree = function(tree, root)
{
    	var list = jQuery("<ul style=\"list-style-type:none;padding-left:0px;margin-left:15px;\"></ul>");
    	if (tree != null) {
		for (var i = 0; i < tree.length; ++i) {
	    		if (tree[i]['parent'] == root) {
				var item = tree[i];
				var expandCollapsePlus = "http://partlink.tw.rpi.edu/ui/images/collapsed.png";
				var expandCollapseMinus = "http://partlink.tw.rpi.edu/ui/images/expanded.png";
				var li = jQuery("<li></li>");
				var table = jQuery("<table></table>");
				var tr = jQuery("<tr></tr>").append("<td><input type=\"hidden\" value=\"" + item['id'] + "\" /><img style=\"cursor:pointer;width:12px\" class=\"more-icon trigger\" src=\"" + expandCollapsePlus + "\"/><img style=\"cursor:pointer;width:12px\" class=\"less-icon trigger\" src=\" "+ expandCollapseMinus + "\"/></td>");
				var td = jQuery("<td><span style=\"cursor:pointer\"></span></td>");
				if (typeof item['count'] != 'undefined') {
					td.find('span').attr('title', item['label'] + " (" + item['count'] + ")");
					td.find('span').html(item['label'] + " (" + item['count'] + ")");
				}
				else {
					td.find('span').attr('title', item['label']);
					td.find('span').html(item['label']);
				} tr.append(td).append("<td><img class=\"info\" id=\"" + item['id'] + "\" src=\"http://partlink.tw.rpi.edu/ui/images/info-icon.png\"/></td>"); table.append(tr);
				li.append(table);
				var subtree = edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.buildTree(tree,item['id']);
				li.find(".more-icon").hide();
				li.find(".less-icon").hide();
				li.find(".no-expand").hide();
				if (jQuery(subtree).children().length > 0) {
					jQuery(li).append(subtree);
                                        subtree.hide();
					jQuery(li).addClass("more");
					jQuery(jQuery(li).find(".more-icon")[0]).show();
				}
				else {
					jQuery(li).addClass("leaf");
					jQuery(li).css({
						"padding-left": "12px"
					});
				}
				jQuery(list).append(li);
	    		}
		}
    	}
    	return list;
}

edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.expandCollapse = function(eventObject)
{
	var obj = jQuery(eventObject.target).parents("li")[0];
    	if (jQuery(obj).hasClass("less")) {
		jQuery(obj).children("ul").hide(); jQuery(obj).addClass("more").removeClass("less");
		jQuery(jQuery(obj).find(".less-icon")[0]).hide(); jQuery(jQuery(obj).find(".more-icon")[0]).show();
    	}
    	else if (jQuery(obj).hasClass("more")) {
		jQuery(obj).children("ul").show(); jQuery(obj).addClass("less").removeClass("more");
		jQuery(jQuery(obj).find(".less-icon")[0]).show(); jQuery(jQuery(obj).find(".more-icon")[0]).hide();
    	}
    	eventObject.stopPropagation();
}

edu.rpi.tw.sesf.s2s.widgets.HierarchyFacetedSelect.showDialog = function(eventObject)
{
	var queryServiceUrlPrefix = "http://partlink.tw.rpi.edu/opensearch/services/query.php?request=";
	var uri = eventObject.target.id;
	jQuery.ajax({
		url: queryServiceUrlPrefix + "part_class_info&input=" + encodeURIComponent(uri)
	}).done(function(data) {
		data = jQuery.parseJSON(data)[0];
		var dialog = jQuery("<div class=\"part-class-info-dialog\" title=\"" + data['label'] + "\"></div>");
		jQuery(dialog).dialog({
			width: 600,
			show: {
				effect: "blind",
				duration: 200
			},
			hide: {
				effect: "blind",
				duration: 200
			}
		});
		dialog.tooltip();
		var table = jQuery("<table class=\"part-class-info-table\"></table>");
		table.append("<tr><td>URI: </td><td>" + uri + "</td></tr>");
		table.append("<tr><td>Label: </td><td>" + data['label'] + "</td></tr>");
		if (typeof data['description'] != 'undefined')
			table.append("<tr><td>Description: </td><td>" + data['description'] + "</td></tr>");
		if (typeof data['native_id'] != 'undefined')
			table.append("<tr><td>Native ID: </td><td>" + data['native_id'] + "</td></tr>");
		if (typeof data['inc'] != 'undefined')
			table.append("<tr><td class=\"tooltip\" title=\"Refers to 5-character item name code.\">INC: </td><td>" + data['inc'] + "</td></tr>");
		jQuery.ajax({
			url: queryServiceUrlPrefix + "part_class_properties&input=" + encodeURIComponent(uri)
		}).done(function(d) {
			d = jQuery.parseJSON(d);
			if (typeof d != 'undefined') {
				jQuery.each(d, function(i, item) {
					var tr = jQuery("<tr></tr>");
					var tdProperty = jQuery("<td></td>");
					if (typeof item['property_label'] != 'undefined')
						tdProperty.html(item['property_label'] + ": ");
					else
						tdProperty.html(item['property'] + ": ");
					if (typeof item['property_description'] != 'undefined') {
						tdProperty.attr('class', 'tooltip'); 
						tdProperty.attr('title', item['property_description']);
					}
					var tdValue = jQuery("<td></td>");
					if (typeof item['value_label'] != 'undefined')
						tdValue.html(item['value_label']);
					else
						tdValue.html(item['value']);
					tr.append(tdProperty).append(tdValue);
					table.append(tr);
				});
				dialog.append(table);
			}
			else 
				dialog.append(table);
		});
	}); 
}
