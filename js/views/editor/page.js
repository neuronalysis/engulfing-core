var PageView = BaseView.extend({
	initialize : function() {
	},
	events : {
		"change" : "changeValue"
	},
	render : function() {
		this.$el.empty();
		
		$(this.el).append(new PrintSpaceView({
			model : this.model.get('PrintSpace')
		}).render().el);
		
		this.$el.css({
			'position'          : 'absolute',
			'width'         	: this.model.get('width') / 3 + 'px',
			'height'        	: this.model.get('height') / 3 + 'px',
			'border'			: '1px solid black'
			});

		return this;
	}
});
