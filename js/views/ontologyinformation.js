var OntologyInformationView = Backbone.View.extend({
	initialize : function() {
		this.template = _.template(tpl.get('layouts/ontologyinformation'));
	},
	render : function() {
		var data = {"ontologyClass": this.model};
		
		this.$el.html(this.template(data));
		
		return this;
	}
});
		