var TextBlockView = BaseView.extend({
	initialize : function() {
		//this.template = _.template(tpl.get('components/editor'));
	},
	render : function() {
		var fontId =  this.model.get('STYLEREFS').split(" ")[1];
		
		var textStyle = window['TextStyle'].findOrCreate({
			id : fontId
		});
		
		this.$el.empty();
		
		this.$el.css({
			'position'      : 'absolute',
			'font-size'		: textStyle.get('FONTSIZE'),
			'font-family'	: textStyle.get('FONTFAMILY'),
			//'width'         : this.model.get('WIDTH') * editorOptions['zoomFactor'] + 'px',
			//'height'        : this.model.get('HEIGHT') * editorOptions['zoomFactor'] + 'px',
			//'left'          : this.model.get('HPOS') * editorOptions['zoomFactor'] + 'px',
			//'top'           : this.model.get('VPOS') * editorOptions['zoomFactor'] + 'px'
		    });
		
		
		if (typeof this.model !== 'undefined') {
			_.each(this.model.get('TextLines').models, function(object) {
				$(this.el).append(new TextLineView({
					model : object
				}).render().el);
			}, this);
		}
		
		return this;
	}
});
