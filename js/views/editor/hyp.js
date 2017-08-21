var HypView = BaseView.extend({
	tagName : 'span',
	
	initialize : function(options) {
		this.parent = options.parent;
	},
	events : {
		"input" : "changeValue"
	},
	changeValue : function(item) {
		this.model.set('CONTENT', item.target.textContent);
		//this.render();
	},
	render : function() {
		this.$el.empty();
		
		this.$el.html(this.model.get('CONTENT'));
		
		var lastString = this.parent.model.getLastString();
		
		var css = {
				'position'          : 'absolute',	
				'width' : 20 + 'px',
				'height' : lastString.get('HEIGHT') * editorOptions['zoomFactor'] + 'px',
				'left' : (parseInt(lastString.get('HPOS')) + parseInt(lastString.get('WIDTH'))) * editorOptions['zoomFactor'] + 'px',
				'top' : lastString.get('VPOS') * editorOptions['zoomFactor'] + 'px',
				'white-space' : 'nowrap'
			};

		css['font-family'] = 'Arial';
		css['font-size'] = this.parent.fontSize;
		
		if (accessMode == "edit") {
			this.$el.attr('contentEditable', true);
		} else {
			this.$el.attr('contentEditable', false);
		}
		
		
		this.$el.css(css);
		
		return this;
	}
});
