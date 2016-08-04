var InputLabelView = InputView.extend({
	initialize : function() {
		this.template = _.template(tpl.get('components/input_label'));
	},
	events : {
		"change" : "changeValue"
	},
	changeValue : function(item) {
		if (typeof this.model.attributes.name === 'undefined') {
			this.model.set(item.target.id, item.target.value);
		} else {
			this.model.set('name', item.target.value);
		}
	},
	render : function() {
		if (typeof this.model.attributes.name === 'undefined') {
			var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "field_value": this.model.get(this.field), "label_name": this.field, "label_value": this.model.get(this.field)};
		} else {
			var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "field_value": this.model.get('OntologyProperty').get('name'), "label_name": this.field, "label_value": this.model.get('name')};
		}
		
		this.$el.html(this.template(data));
		
		return this;
	}
});
		