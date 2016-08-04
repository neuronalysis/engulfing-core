var ButtonDeleteView = ButtonView.extend({
	initialize : function() {
		this.template = _.template(tpl.get('components/button_delete'));
	},
	events : {
	},
	render : function() {
		this.$el.html(this.template());
		
		return this;
	}
});
		