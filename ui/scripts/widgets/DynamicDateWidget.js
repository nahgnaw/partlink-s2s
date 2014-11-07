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
edu.rpi.tw.sesf.s2s.widgets.DynamicDateWidget = function(panel) {
	this.panel = panel;
	this.availableDates = {};
	var i = panel.getInput();
	var input = jQuery("<input type=\"text\"></input>");
	var clearButton = jQuery("<button class='clear-button ui-state-default ui-corner-all' type='button'>Clear</button>");
	this.div =  jQuery("<div class=\"facet-content\"></div>");
	var self = this;
	jQuery(input).datepicker({ 
		dateFormat: 'yy-mm-dd', 
		//beforeShowDay: this.checkAvailableDates 
	});
	jQuery(input).datepicker("option", "changeMonth", true);
	jQuery(input).datepicker("option", "changeYear", true);
	jQuery(input).datepicker("option", "yearRange", '-99:+99');
	jQuery(input).datepicker("option", "showButtonPanel", true);
	panel.setInputData(i.getId(), function() {
		return jQuery(input).val();
	});
	jQuery(input).change(function() {
		self.updateState();
		panel.notify();
	});
	jQuery(clearButton).click(function() {
		jQuery(input).val('').change();
	});
	jQuery(this.div).append(input).append(clearButton);
}

edu.rpi.tw.sesf.s2s.widgets.DynamicDateWidget.prototype.updateState = function(clicked)
{
	this.state = this.div.find("input").val();
}

edu.rpi.tw.sesf.s2s.widgets.DynamicDateWidget.prototype.getState = function()
{
	return this.state;
}

edu.rpi.tw.sesf.s2s.widgets.DynamicDateWidget.prototype.setState = function(state)
{
	this.div.find("input").val(state);
}

edu.rpi.tw.sesf.s2s.widgets.DynamicDateWidget.prototype.checkAvailableDates = function(date)
{
	var month = date.getMonth();
	var year = date.getFullYear();
	var day = date.getDate();
	var dateStr = year + "-" + ((month > 8) ? (month + 1) : ("0" + (month + 1))) + "-" + ((day > 9) ? day : ("0" + day)); 
	if (dateStr in  this.availableDates) {
		return [ true, '' ];
	}
	else {
		return [ false, '' ];
	}
}

edu.rpi.tw.sesf.s2s.widgets.DynamicDateWidget.prototype.get = function()
{
	return this.div;
}

edu.rpi.tw.sesf.s2s.widgets.DynamicDateWidget.prototype.reset = function()
{
	this.availableDates = {};
}

edu.rpi.tw.sesf.s2s.widgets.DynamicDateWidget.prototype.update = function(data)
{
	var obj = JSON.parse(data);
	for (var i in obj)
	{
	    var date = obj[i]['date'];
	    this.availableDates[date] = 1;
	}
}
