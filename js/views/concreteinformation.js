var ConcreteInformationView = Backbone.View.extend({
	initialize : function() {
		this.template = _.template(tpl.get('layouts/concreteinformation'));
	},
	render : function() {
		var data = {"object": this.model};
		
		this.$el.html(this.template(data));
		
		return this;
	}
});
		