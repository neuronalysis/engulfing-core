var DatePickerView = InputView.extend({
	
	initialize : function() {
		DatePickerView.__super__.initialize.apply(this, arguments);

		this.template = _.template(tpl.get('components/input_datepicker'));
	},
	events : {
		"changeDate": "changeValue"
	},
	changeValue : function(item) {
		var date = new Date(item.date);
		
		var day = date.getDate();
		var monthIndex = date.getMonth() +1 ;
		var year = date.getFullYear();

		this.model.set(item.target.id, year + '-' +  monthIndex + '-' + day);
	},
	render : function() {
		var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "field_value": this.model.get(this.field)};

		this.$el.html(this.template(data));
		
		this.$("#" + this.field).datepicker({
			format: "yyyy-mm-dd",
		    autoclose: true
		});
		
		if (this.model.get(this.field)) {
			var dateval = this.model.get(this.field).substring(0,10);
		} else {
			var dateval = "";
		}
		
		this.$("#" + this.field).datepicker('update', dateval);
		
		return this;
	}
});
		