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
edu.rpi.tw.sesf.s2s.widgets.ResultsListWidget = function(panel) {
	this.panel = panel;
	this.div = jQuery("<div style=\"display:inline\"><div style=\"float:right;\" class=\"paging-panel\"></div><br/><div style=\"margin-top:3px\" class=\"html\"><span>Loading...</span></div></div>");
	var offsetInput = edu.rpi.tw.sesf.s2s.utils.offsetInput;
	var limitInput = edu.rpi.tw.sesf.s2s.utils.limitInput;
	var self = this;
	panel.setInputData(offsetInput, function() {
		return self.offset;
	});
	panel.setInputData(limitInput, function() {
		return self.limit;
	});
	this.limit = this.panel.getInterface().getDefaultLimit();
	this.offset = 0;
}

edu.rpi.tw.sesf.s2s.widgets.ResultsListWidget.prototype.updateState = function()
{
	this.state = {"limit":this.limit,"offset":this.offset};
}

edu.rpi.tw.sesf.s2s.widgets.ResultsListWidget.prototype.setState = function(state)
{
	this.panel.notify(true, true, true);
}

edu.rpi.tw.sesf.s2s.widgets.ResultsListWidget.prototype.getState = function()
{
	return this.state;
}

edu.rpi.tw.sesf.s2s.widgets.ResultsListWidget.prototype.get = function()
{
	return this.div;
}

edu.rpi.tw.sesf.s2s.widgets.ResultsListWidget.prototype.myReset = function()
{
	jQuery(this.div).find(".html").html("");
	jQuery(this.div).find(".html").append("<span>Loading...</span>");
	jQuery(this.div).find(".paging-panel").children().remove();
}

edu.rpi.tw.sesf.s2s.widgets.ResultsListWidget.prototype.reset = function()
{
	this.myReset();
	this.limit = this.panel.getInterface().getDefaultLimit();
	this.offset = 0;
}

edu.rpi.tw.sesf.s2s.widgets.ResultsListWidget.prototype.update = function(data)
{
	var queryServiceUrlPrefix = "http://partlink.tw.rpi.edu/opensearch/services/query.php?request=";
	jQuery(this.div).find(".html").children().remove();
	jQuery(this.div).find(".page").children().remove();
	jQuery(this.div).find(".html").append(data);

	var self = this;

	jQuery(".expander-head").click(function() {
		var content = jQuery(this).next(".expander-content");
        	content.slideToggle();
		
		if (jQuery(this).find(".expander-action").text() == "expand") {
			jQuery(this).find(".expander-action").html("collape");
			var style = {
				"background-position": "31% 50%",
        			"background-repeat": "no-repeat",
				"background-size": "12px 12px",
        			"background-image": "url(images/expanded.png)"
			};
			jQuery(this).css(style);
			
			content.children().remove();
			content.append("<span>Loading...</span>");	
			var uri = jQuery(this).parent().parent().attr('id');
			jQuery.ajax({
				url: queryServiceUrlPrefix + "order_lines&input=" + encodeURIComponent(uri)
			}).done(function(data) {
				data = jQuery.parseJSON(data);
				content.children().remove();
				var table = jQuery("<table class=\"order-lines\"></table>").appendTo(content);
				var map = jQuery("<div class=\"cage-map\"><span>Loading...</span></div>");
				content.append("<p>CAGE Information:</p>").append(map);
				table.append("<tr><th style=\"width:8%\">Line Number</th><th style=\"width:12%\">NIIN</th><th style=\"width:18%\">Contract Number</th><th style=\"width:8%\">Net Price</th><th style=\"width:10%\">Order Quantity</th><th style=\"width:8%\">Unit of Issue</th><th style=\"width:12%\">Award Date</th><th style=\"width:12%\">Purchase Order Change Date</th><th style=\"width:12%\">Data in DB Date</th></tr>");
				jQuery.each(data, function(i, item) {
					table.append("<tr><td>" + item['line_number'] + "</td><td><span class=\"niin\" id=\"" + item['niin'] + "\">"  + item['niin'].substring(35) + "</span></td><td>" + item['contract_number'] + "</td><td>" + item['net_price'] + "</td><td>" + item['order_quantity'] + "</td><td>" + item['unit_of_issue'] + "</td><td>" + item['award_date'] + "</td><td>" + item['purchase_order_change_date'] + "</td><td>" + item['data_in_db_date'] + "</td></tr>");
				});
				var cageData = {uri: data[0]['cage']};
				jQuery.ajax({
					url: queryServiceUrlPrefix + "cage_info&input=" + encodeURIComponent(cageData.uri)
				}).done(function(d){
					d = jQuery.parseJSON(d);
					jQuery.each(d[0], function(key, value) {
						cageData[key] = value;
					});
					var mapDiv = content.find(".cage-map")[0];
					jQuery(mapDiv).tooltip();
					self.initializeMap(mapDiv, cageData);
				});

				jQuery("span.niin").click(function() {
					var niin = this.id;
					var dialog = jQuery("<div class=\"niin-info-dialog\" title=\"NIIN: " + niin.substring(35) + "\"></div>");
					dialog.append("<p>Loading...</p>");
					dialog.dialog({
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
					jQuery.ajax({
						url: queryServiceUrlPrefix + "niin_info&input=" + encodeURIComponent(niin)
					}).done(function(data) {
						data = jQuery.parseJSON(data);
						if (typeof data == 'undefined') 
							dialog.append("<p>No information returned for this NIIN. Please try to click it again.</p>");
						else {
							dialog.children().remove();
							/*
							if (data.hierarchy.length > 0) {
								var div = jQuery("<div class=\"niin-info-hierarchy\"></div>");
								div.append("<p style=\"font-weight:bold\">NIIN Hierarchy</p>");
								dialog.append(div);
							}
							*/
							if (data.logistics.length > 0) {
								var logistics = data['logistics'];
								var table = jQuery("<table class=\"niin-info-logistics\"></table>");
								table.append("<tr><th colspan=\"2\"><span>NIIN Logistics Information</span></th></tr>");
								jQuery.each(logistics, function(i, item) {
									var tr = jQuery("<tr></tr>");
									var tdProperty = jQuery("<td style=\"width:50%\"></td>");
									if (typeof item['property_label'] != 'undefined')
										tdProperty.html(item['property_label'] + ": ");
									else 
										tdProperty.html(item['property'].split('#')[1].split(/(?=[A-Z])/).join(' ') + ": ");
									if (typeof item['property_comment'] != 'undefined') {
										tdProperty.attr('class', 'tooltip');
										tdProperty.attr('title', item['property_comment']);
									}
									var tdValue = jQuery("<td></td>");
									if (typeof item['value_code'] != 'undefined')
										tdValue.html(item['value_code']);
									else if (typeof item['value_label'] != 'undefined')
										tdValue.html(item['value_label']);
									else
										tdValue.html(item['value']);
									if (typeof item['value_definition'] != 'undefined') {
										tdValue.attr('class', 'tooltip');
										tdValue.attr('title', item['value_definition']);
									}
									tr.append(tdProperty).append(tdValue);
									table.append(tr);
								}); 
								dialog.append(table);
							}
							if (data.ref_num.length > 0) {
								var refNum = data['ref_num'];
								var div = jQuery("<div class=\"niin-info-ref-num\"></div>");
								div.append("<p style=\"font-weight:bold;margin-bottom:3px\">NIIN Reference Numbers</p>");
								jQuery.each(refNum, function(i, item) {
									var table = jQuery("<table class=\"niin-info-ref-num\"></table>");
									table.append("<tr><td colspan=\"2\" style=\"font-style:italic\">" + decodeURIComponent(item['ref_num'].split('#')[1]) + "</td></tr>");
									var trCage = jQuery("<tr><td style=\"width:15%\">CAGE: </td><td id=\"" + item['cage'] + "\">" + item['cage_name'] + "</td></tr>");
									var trPartNum = jQuery("<tr><td>Part Number: </td><td>" + item['part_number'] + "</td></tr>");
									var trRnccrnvc = jQuery("<tr><td class=\"tooltip\" title=\"" + item['rnccrnvc_comment'] + "\">RNCCRNVC: </td><td>" + item['rnccrnvc'] + "</td></tr>");
									table.append(trCage).append(trPartNum).append(trRnccrnvc);
									div.append(table);
									
								});
								dialog.append(div);
							}
							if (typeof data['product'][0].length == 'undefined') {
								var product = data['product'];
								var table = jQuery("<table class=\"niin-info-product\"></table>");
								table.append("<tr><th colspan=\"2\">NIIN Part Properties</th></tr>");
								jQuery.each(product, function(i, item) {
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
									var values = item['value'].split(';');
									jQuery.each(values, function(j, value) {
										jQuery.ajax({
											url: queryServiceUrlPrefix + "product_property_value_info&input=" + encodeURIComponent(value)
										}).done(function(data) {
											data = jQuery.parseJSON(data);
											if (data.length > 0) {
												var p = jQuery("<p>* </p>");
												jQuery.each(data, function(i, d) {
													var span = jQuery("<span></span>");
													var v = '';
													if (typeof d['value_label'] != 'undefined')
														span.html("\"" + d['value_label'] + "\" ");
													else
														span.html("\"" + d['value'] + "\" ");
													if (typeof d['property_label'] != 'undefined') { 
														span.attr('class', 'tooltip');
														span.attr('title', d['property_label']);
													}
													p.append(span);			
												});
												tdValue.append(p)
											}
										});
									});
									tr.append(tdProperty).append(tdValue);
									table.append(tr);
								});
								dialog.append(table);
							}
						}
					});
				});
			});
		}
		else {
			jQuery(this).find(".expander-action").html("expand");
			var style = {
				"background-position": "31% 50%",
        			"background-repeat": "no-repeat",
				"background-size": "12px 12px",
        			"background-image": "url(images/collapsed.png)"
			};
                        jQuery(this).css(style);
		}

        });


    	var nextCallback = function() {
		if (self.limit == null)
		{
			var limit = jQuery(self.div).find("[name=\"itemsPerPage\"]");
			var offset = jQuery(self.div).find("[name=\"startIndex\"]");
			if (limit.length > 0) 
			{
				self.limit = parseInt(limit.val());
			}
			if (offset.length > 0)
			{
				self.offset = parseInt(offset.val()) + self.limit;
			}
		}
		else
		{
			self.offset = self.offset + self.limit;
		}
		self.myReset();
		self.updateState();
		self.panel.notify(true, true, true);
    	}

    	var prevCallback = function() {
		if (self.limit == null)
		{
			var limit = jQuery(self.div).find("[name=\"itemsPerPage\"]");
			var offset = jQuery(self.div).find("[name=\"startIndex\"]");
			if (limit.length > 0) 
			{
				self.limit = parseInt(limit.val());
			}
			if (offset.length > 0)
			{
				self.offset = parseInt(offset.val()) - self.limit;
			}
		}
		else
		{
			self.offset = self.offset - self.limit;
		}
		self.myReset();
		self.updateState();
		self.panel.notify(true, true, true);
    	}

    	/**
     	* Create paging panel
     	*/
    	var prev = jQuery("<div title=\"prev\" style=\"margin-left:3px;border:1px solid;background:#E6E6E6;display:inline\"><span style=\"width:4em;align:center\">&nbsp;&lt;&nbsp;</span></div>");
    	var next = jQuery("<div title=\"next\" style=\"margin-left:1px;border:1px solid;background:#E6E6E6;display:inline\"><span style=\"width:4em;align:center\">&nbsp;&gt;&nbsp;</span></div>");
    	var start = jQuery("<b></b>");
    	var end = jQuery("<b></b>");
    	var total = jQuery("<b></b>");
    	var paging = jQuery("<span></span>").append(start).append("-").append(end).append(" of ").append(total);
    	var config = jQuery("<select style=\"margin-left:5px\"><option>10</option><option>20</option><option>50</option><option>100</option><option>200</option></select>");
    	var panel = jQuery("<span></span>").append(paging).append(prev).append(next).append(config);

    	jQuery(config).change(function() {
 		self.limit = parseInt(jQuery(this).val());
		self.offset = 0;
		self.myReset();
		self.updateState();
		self.panel.notify(true, true, true);
    	});

	/**
	 * Get page info
	 */
	if (this.offset == null) {
		var offset = jQuery(this.div).find("[name=\"startIndex\"]");
		if (offset.length > 0) {
			this.offset = parseInt(offset.val());
		}
	}
	
	if (this.offset != null && this.offset > 0) {
		jQuery(prev).click(prevCallback);
	    jQuery(prev).css('cursor','pointer');
	} else {
		jQuery(prev).unbind('click');
    	jQuery(prev).css('opacity','0.4');
    	jQuery(prev).css('filter','alpha(opacity=40)');
    	jQuery(prev).css('cursor','auto');
	}

	if (this.limit == null) {
		var limit = jQuery(this.div).find("[name=\"itemsPerPage\"]");
		if (limit.length > 0) {
			this.limit = parseInt(limit.val());
		}
	}
	
	if (this.limit != null) {
		jQuery(next).click(nextCallback);
	    jQuery(next).css('cursor','pointer');
	} else {
		jQuery(prev).unbind('click');
    	jQuery(prev).css('opacity','0.4');
    	jQuery(prev).css('filter','alpha(opacity=40)');
    	jQuery(prev).css('cursor','auto');
	}

    	var results = jQuery(this.div).find("[name=\"totalResults\"]");
    	if (results != null) {
		results = parseInt(results.val());
		jQuery(config).children().each(function() {
			if (parseInt(jQuery(this).val()) == self.limit) jQuery(this).attr("selected","selected");
		});
		jQuery(start).html("" + (this.offset + 1));
		jQuery(end).html("" + (this.offset + this.limit));
		jQuery(total).html("" + results);
		if ((this.offset + this.limit) >= results) {
	    	jQuery(next).unbind('click');
	    	jQuery(next).css('opacity','0.4');
	    	jQuery(next).css('filter','alpha(opacity=40)');
	    	jQuery(next).css('cursor','auto');
	    	jQuery(end).html("" + results);
		}
		jQuery(this.div).find(".paging-panel").append(panel);
    	}
}

edu.rpi.tw.sesf.s2s.widgets.ResultsListWidget.prototype.initializeMap = function(div, data)
{
	var localityAddress = data['locality'] + ", " + data['region'] + ", " + data['country'];
	var geocoder = new google.maps.Geocoder();
	geocoder.geocode({'address': localityAddress}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			var resultLatLng = results[0].geometry.location;
			var mapOptions = {
				center: ({lat: resultLatLng.lat() + 0.26, lng: resultLatLng.lng()}),
				zoom: 9 
			};
			var map = new google.maps.Map(div, mapOptions);
			var marker = new google.maps.Marker({
				map: map,
				position: results[0].geometry.location
			});			
			var infoWindowContent = "<div class=\"cage-map-info-window-content\"><span>Name: </span>" + data['name'];
			if (typeof data['code'] != 'undefined')
				infoWindowContent += "<br /><span class=\"tooltip\" title=\"Five-character CAGE code\">Code: </span>" + data['code'];
			if (typeof data['status'] != 'undefined')
				infoWindowContent += "<br /><span class=\"tooltip\" title=\"Used to indicate cage status code for CAGE\">Status: </span>" + data['status'].split('_')[1];
			if (typeof data['business_type_code'] != 'undefined')
				infoWindowContent += "<br /><span class=\"tooltip\">Business Type Code: </span>" + data['business_type_code'].split('_')[1];
			if (typeof data['business_size_code'] != 'undefined')
				infoWindowContent += "<br /><span class=\"tooltip\">Business Size Code: </span>" + data['business_size_code'].split('_')[1];
			if (typeof data['primary_business_code'] != 'undefined')
				infoWindowContent += "<br /><span class=\"tooltip\">Primary Business Code: </span>" + data['primary_business_code'].split('_')[1];
			if (typeof data['cao'] != 'undefined')
				infoWindowContent += "<br /><span class=\"tooltip\" title=\"Contract Activity Office\">CAO: </span>" + data['cao'];
			if (typeof data['adp'] != 'undefined')
				infoWindowContent += "<br /><span class=\"tooltip\" title=\"Automated Data Processing\">ADP: </span>" + data['adp']; 
			if (typeof data['duns'] != 'undefined')
				infoWindowContent += "<br /><span class=\"tooltip\" title=\"A unique, non-indicative 9-digit identifier issued and maintained by Dunn and Bradstreet (D&B) that verifies the existence of a business entity globally.\">DUNS Num: </span>" + data['duns'];
			if (typeof data['woman_owned'] != 'undefined')
				infoWindowContent += "<br /><span>Is Woman Owned: </span>" + data['woman_owned'];
			if (typeof data['street_address'] != 'undefined') {
				infoWindowContent += "<br /><span>Address: </span>" + data['street_address'];
				if (typeof data['locality'] != 'undefined')
					infoWindowContent += ", " + data['locality'];
				if (typeof data['region'] != 'undefined')
					infoWindowContent += ", " + data['region'];
				if (typeof data['country'] != 'undefined')
					infoWindowContent += ", " + data['country'];
			} 
			if (typeof data['postal_code'] != 'undefined')
				infoWindowContent += "<br /><span>Postal Code: </span>" + data['postal_code'];
			if (typeof data['associated_cage'] != 'undefined')
				infoWindowContent += "<br /><span>Associated CAGE: </span>" + data['associated_cage'];
			infoWindowContent += "</div>";
			var infoWindow = new google.maps.InfoWindow({
				content: infoWindowContent
			});
			infoWindow.open(map, marker);
			google.maps.event.addListener(marker, 'click', function() {
				infoWindow.open(map, marker);
			});
		} else {
			div.children().remove();
			div.append("<p>No CAGE information.</p>");	
		}
	});

}
