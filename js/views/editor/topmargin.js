var TopMarginView = BaseView.extend({
	tagName : 'topmargin',
	
	initialize : function() {
	},
	events : {
		"change" : "changeValue"
	},
	render : function() {
		$(this.el).empty();
		
		/*var css = {
				'position' : 'relative',	
				'width' : this.model.get('WIDTH') * editorOptions['zoomFactor'] + 'px',
				'height' : this.model.get('HEIGHT') * 1.3 * editorOptions['zoomFactor'] + 'px',
				'left' : this.model.get('HPOS') * editorOptions['zoomFactor'] + 'px',
				'top' : this.model.get('VPOS') * editorOptions['zoomFactor'] + 'px',
				'white-space' : 'nowrap'
			};
		*/
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
		
		//this.$el.css(css);
		
		return this;
	}
});
