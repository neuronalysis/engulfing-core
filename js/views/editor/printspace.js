var PrintSpaceView = BaseView.extend({
	tagName : 'printspace',
	
	initialize : function() {
	},
	events : {
		"change" : "changeValue"
	},
	render : function() {
		$(this.el).empty();
		
		_.each(this.model.get('TextBlocks').models, function(object) {
			$(this.el).append(new TextBlockView({
				model : object
			}).render().el);
		}, this);
		
		_.each(this.model.get('ComposedBlocks').models, function(object) {
			$(this.el).append(new ComposedBlockView({
				model : object
			}).render().el);
		}, this);
		
		return this;
	}
});
