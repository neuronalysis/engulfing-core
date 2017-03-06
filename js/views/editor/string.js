var StringView = BaseView.extend({
	tagName : 'span',
	
	initialize : function() {
		//this.template = _.template(tpl.get('components/editor'));
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

		var css = {
				'position'          : 'absolute',	
				'width' : this.model.get('WIDTH') / 3 + 'px',
				'height' : this.model.get('HEIGHT') / 3 + 'px',
				'left' : this.model.get('HPOS') / 3 + 'px',
				'top' : this.model.get('VPOS') / 3 + 'px',
				'white-space' : 'nowrap'
			};

		if (this.model.get('style')) {
			var style = this.model.get('style');
			
			if (style == 'bold') {
				css['font-weight'] = 'bold';
			} else if (style == 'italics') {
				css['font-style'] = 'italic';
			} else {
				css['font-style'] = style;
			}
		}
		
		if (accessMode == "edit") {
			this.$el.attr('contentEditable', true);
		} else {
			this.$el.attr('contentEditable', false);
		}
		
		
		this.$el.css(css);
		
		
		
		return this;
	}
});
