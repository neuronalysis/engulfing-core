var InputTextView = InputView.extend({
	initialize : function(options) {
		InputTextView.__super__.initialize.apply(this, arguments);
		
		this.template = _.template(tpl.get('components/input_text'));
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
		var field_value = '';
		
		if (typeof this.model.type === 'undefined') {
			this.model.type = 'ReleasePublication';
		}
    		
		if (accessMode == "edit") {
			field_value += this.model.get(this.field);
		} else {
			if (this.model.type === "OntologyPropertyEntity") {
				if (this.url) {
					field_value += '<a href="' + this.url + '">' + this.model.get(this.field) + '</a>';
				} else {
					field_value += this.model.get('name');
				}
			} else {
				if (this.url) {
					field_value += '<a href="' + this.url + '">' + this.model.get(this.field) + '</a>';
				} else {
					field_value += this.model.get(this.field);
				}
			}
		}
		
		
		var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "field_value": field_value, "withLabel" : this.withLabel, "labelName" : this.labelName};

		this.$el.html(this.template(data));
		
		return this;
	}
});
		