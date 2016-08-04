var InputCheckBoxView = InputView.extend({
	
	initialize : function() {
		this.template = _.template(tpl.get('components/input_checkbox'));
	},
	events : {
		"click" : "changeValue"
	},
	changeValue : function(item) {
		this.model.set(item.target.id, item.target.checked);
	},
	render : function() {
		var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "field_value": this.model.get(this.field)};

		this.$el.html(this.template(data));
		
		return this;
	}
});
		