var StringView = BaseView.extend({
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
		} else {
			css['font-family'] = 'Arial';
			css['font-size'] = this.parent.fontSize;
			
			
			/*var textsize = get_text_size(this.model.get('CONTENT'), css['font-size'] + " " + css['font-family']);
			
			var heightDif = parseInt((this.model.get('HEIGHT') / 3) - textsize['height']);
			if (this.model.get('CONTENT') == 'vertreten.') {
				alert (this.parent.model.get('BASELINE'));
				alert (parseInt(heightDif));
			}
			
			if (heightDif == 0) {
				if (textsize['width'] > (this.model.get('WIDTH') / 3)) {
					//css['font-family'] = 'Arial Narrow';
				}
			} else {
				css['font-size'] = (14 + heightDif) + 'px';
			}*/
			
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
