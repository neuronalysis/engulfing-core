var ButtonView = Backbone.View.extend({
	tagName : "button",
	className : "btn btn-sm btn-primary pull-right",
	
	buttonObjectName : "",
	
	label : null,
	targetID : null,
	disabled : false,
	
	initialize : function(options) {
		this.targetID = options.targetID;
		
		this.label = options.label;
	},
	events : {
		"click" : "click",
		
	},
	click : function () {
		var id_splitted = this.id.split("_");
		
		if (id_splitted[1] === "context") {
			this.start();
		}
		return id_splitted[1].charAt(0).toUpperCase() + id_splitted[1].slice(1);
	},
	start : function () {
		var service = new Service({
			ontology : "edi",
			resource : "import/data",
			parameterName : "importprocessID",
			parameterValue : this.id
		});
		service.fetch();
	},
	getLabel : function () {
		if (this.label) {
			return this.label;
		} else {
			var id_splitted = this.id.split("_");

			return id_splitted[1].charAt(0).toUpperCase() + id_splitted[1].slice(1) + ' ' + this.buttonObjectName;
		}
	},
	importDataService : function() {
		alert('ass');
	},
	render : function() {
		this.$el.prop("disabled", this.disabled);
		
		this.$el.attr('id', this.id);
		this.$el.attr('targetID', this.targetID);
		
		this.$el.html(this.getLabel());
		
		return this;
	}
});
		