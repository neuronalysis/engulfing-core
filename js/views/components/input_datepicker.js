var DatePickerView = InputView.extend({
	
	initialize : function() {
		this.template = _.template(tpl.get('components/input_datepicker'));
	},
	events : {
		//"click" : "showDatepicker",
		"change" : "changeValue"
	},
	showDatepicker : function () {
		//$("#" + this.field).daterangepicker();
	},
	changeValue : function(item) {
		this.model.set(item.target.parentNode.id, item.target.value);
		
		this.$("#" + this.field).datepicker('hide');
		this.$("#" + this.field).datepicker('place');
	},
	render : function() {
		var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "field_value": this.model.get(this.field)};

		this.$el.html(this.template(data));
		
		this.$("#" + this.field).datepicker();
		
		return this;
	}
});
		