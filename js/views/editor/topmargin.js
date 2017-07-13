var TopMarginView = BaseView.extend({
	initialize : function() {
	},
	events : {
		"change" : "changeValue"
	},
	render : function() {
		$(this.el).empty();
		
		if (this.model) {
			if (this.model.get('TextBlocks')) {
				_.each(this.model.get('TextBlocks').models, function(object) {
					$(this.el).append(new TextBlockView({
						model : object
					}).render().el);
					
				}, this);
			}
			
			if (this.model.get('ComposedBlocks')) {
				_.each(this.model.get('ComposedBlocks').models, function(object) {
					$(this.el).append(new ComposedBlockView({
						model : object
					}).render().el);
				}, this);
			}
		}
		
		
		return this;
	}
});
