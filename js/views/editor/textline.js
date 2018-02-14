var TextLineView = BaseView.extend({
	tagName : 'textline',
	
	initialize : function(options) {
		this.parent = options.parent;
		
		this.fontCSS = this.model.getFontCSS();
	},
	render : function() {
		var view = ALTOEditorView;
		
		this.$el.empty();
		
		if (this.model.get('STYLEREFS')) {
			var fontId =  this.model.get('STYLEREFS').split(" ")[1];
			
			
			var textStyle = window['TextStyle'].findOrCreate({
				id : fontId
			});
			
			var css = {
					'position'          : 'absolute',
					'width'         : this.model.get('WIDTH') * editorOptions['zoomFactor'] + 'px',
					'height'        : 1 * editorOptions['zoomFactor'] + 'px',
					'left'          : this.model.get('HPOS') * editorOptions['zoomFactor'] + 'px',
					'top'           : this.model.get('VPOS') * editorOptions['zoomFactor'] + 'px'
				};
		} else {
			var css = {
					'position' : 'absolute',	
					'width' : this.model.get('WIDTH') * editorOptions['zoomFactor'] + 'px',
					'height' : 0 * editorOptions['zoomFactor'] + 'px',
					'left' : this.model.get('HPOS') * editorOptions['zoomFactor'] + 'px',
					'top' : this.model.get('VPOS') * editorOptions['zoomFactor'] + 'px'
				};
		}
		
		_.each(this.model.get('Strings').models, function(object) {
			if (object.get('SUBS_TYPE') === 'HypPart1') {
				object.set('CONTENT', object.get('CONTENT'));
				var stringView = new StringView({
					model : object,
					parent : this
				});
			
				$(this.el).append(stringView.render().el);
				
				var hypView = new HypView({
					model : new HYP({"CONTENT": "-"}),
					parent : this
				});
				
				$(this.el).append(hypView.render().el);
			} else {
				var stringView = new StringView({
					model : object,
					parent : this
				});
			
				$(this.el).append(stringView.render().el);
			}
		}, this);
		
		_.each(this.model.get('SPs').models, function(object) {
			$(this.el).append(new SPView({
				model : object
			}).render().el);
		}, this);
		
		this.$el.css(css);
		
		return this;
	}
});
