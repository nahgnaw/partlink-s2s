/** * Create Namespace
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
edu.rpi.tw.sesf.s2s.widgets.RangeSliderWidget = function(panel) {
	this.panel = panel;
	this.delimiter = " ~ ";
	var i = panel.getInput();
	var label = jQuery("<label for=\"range\">Range:</label>");
	var input = jQuery("<input type=\"text\" class=\"range\"></input>");
	var slider = jQuery("<div class=\"slider\"></div>");
	var range = jQuery("<div class=\"range\"><span class=\"min\"></span><span class=\"max\"></span></div>");
	this.div = jQuery("<div class=\"facet-content\"></div>");
	var self = this;
	panel.setInputData(i.getId(), function() {
		return jQuery(input).val();
	});
	
	jQuery(input).change(function() {
		self.updateState();
		panel.notify();
	});
	jQuery(this.div).append(label).append(input).append(slider).append(range);
}

edu.rpi.tw.sesf.s2s.widgets.RangeSliderWidget.prototype.updateState = function(clicked)
{
	this.state = this.div.find("input").val();
}

edu.rpi.tw.sesf.s2s.widgets.RangeSliderWidget.prototype.getState = function()
{
	return this.state;
}

edu.rpi.tw.sesf.s2s.widgets.RangeSliderWidget.prototype.setState = function(state)
{
	this.div.find("input").val(state);
}

edu.rpi.tw.sesf.s2s.widgets.RangeSliderWidget.prototype.get = function()
{
	return this.div;
}

edu.rpi.tw.sesf.s2s.widgets.RangeSliderWidget.prototype.reset = function()
{
	this.div.find("input").val("");
	this.div.find("div.range span").html("...");
}

edu.rpi.tw.sesf.s2s.widgets.RangeSliderWidget.prototype.update = function(data)
{
	var obj = JSON.parse(data);
	var min = parseFloat(obj[0]['min']);
	var max = parseFloat(obj[0]['max']);
	var prefix = obj[0]['prefix'];
	this.div.find("span.min").html(min);
	this.div.find("span.max").html(max);
		
	var self = this;
	jQuery(function() {
    		self.div.find(".slider").slider({
      			range: true,
      			min: min,
      			max: max,
			step: 10,
      			values: [min, max],
      			slide: function(event, ui) {
        			self.div.find("input").val(ui.values[0] + self.delimiter + ui.values[1]);
      			},
      			stop: function(event, ui) {
        			self.div.find("input").val(ui.values[0] + self.delimiter + ui.values[1]).change();
      			}
    		});
    		//self.div.find("input").val(self.div.find(".slider").slider("values", 0) + self.delimiter + self.div.find(".slider").slider("values", 1));
  	});
}
