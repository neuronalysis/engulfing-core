var ButtonAddNewView = BaseView.extend({
	initialize : function() {
		this.template = _.template(tpl.get('components/button_addnew'));
	},
	events : {
	},
	/*addnewObject : function(item) {
        app.navigate('new', true);
	},*/
	render : function() {
		this.$el.html(this.template());
		
		return this;
	}
});
		