var PageView = BaseView.extend({
	initialize : function() {
	},
	events : {
		"change" : "changeValue"
	},
	render : function() {
		this.$el.empty();
		
		var css = {
				'top'         		: '0px',
				'left'         		: '0px',
				'width'         	: this.model.get('WIDTH') * editorOptions['zoomFactor'] + 'px',
				'height'        	: this.model.get('HEIGHT') * editorOptions['zoomFactor'] + 'px',
				'border'			: '1px solid black'
			};
	
		$(this.el).append(new TopMarginView({
			model : this.model.get('TopMargin')
		}).render().el);
		
		$(this.el).append(new PrintSpaceView({
			model : this.model.get('PrintSpace')
		}).render().el);
		
		this.$el.css(css);
		
		return this;
	}
});
