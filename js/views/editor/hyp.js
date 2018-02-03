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
	},
	render : function() {
		this.$el.empty();
		
		this.$el.html(this.model.get('CONTENT'));
		
		var lastString = this.parent.model.getLastString();
		
		var css = {
				'position' : 'absolute',	
				'width' : this.model.get('WIDTH') * editorOptions['zoomFactor'] + 'px',
				'height' : this.parent.model.get('HEIGHT') * editorOptions['zoomFactor'] + 'px',
				'left' : (parseInt(lastString.get('HPOS')) + parseInt(lastString.get('WIDTH')) - parseInt(this.parent.model.get('HPOS'))) * editorOptions['zoomFactor'] + 'px',
				'top' : '4px',
				
				'white-space' : 'nowrap',
				'display':'inline-block'
			};

		
		if (accessMode == "edit") {
			this.$el.attr('contentEditable', true);
		
			this.$el.append('<div style="position: absolute; top: 1px; left: 0px; outline: gainsboro solid 1px; width: 10px' + '; height: ' + lastString.get('HEIGHT') * editorOptions['zoomFactor'] + 'px' + '; z-index: -1;"></div>')
			
		} else {
			this.$el.attr('contentEditable', false);
		}
		
		
		css['font-family'] = this.parent.fontCSS['font-family'];
		css['font-size'] = this.parent.fontCSS['font-size'];
		
		this.$el.css(css);
		
		return this;
	}
});
