var InputImageView = InputView.extend({
	initialize : function() {
		this.template = _.template(tpl.get('components/input_image'));
	},
	render : function() {
		var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "field_value": this.model.get(this.field), "label_name": this.field, "label_value": this.model.get(this.field)};
		
		
		this.$el.html(this.template(data));
		
		return this;
	}
});
		