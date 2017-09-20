var StringView = BaseView.extend({
	tagName : 'span',
	
	initialize : function(options) {
		this.parent = options.parent;
	},
	events : {
		"input" : "changeValue",
		"mouseover" : "hooverArea"
	},
	//TODO doublicate implemenation in tracker.js - consolidate
	hooverArea : function() {
		if (editorOptions['imageAvailable'] && editorOptions['facsimileVisibility']) {
			let xCoor = +this.model.get('HPOS');
			let yCoor = +this.model.get('VPOS');
			
			let hooverTop = 0 + (+yCoor * editorOptions['zoomFactor']);
			let hooverLeft = 0 + (+xCoor * editorOptions['zoomFactor']);
			
			$("#hooverCraft").css({
				'position' : 'absolute',	
				'width' : this.model.get('WIDTH') * editorOptions['zoomFactor'] + 'px',
				'height' : this.model.get('HEIGHT') * editorOptions['zoomFactor'] + 'px',
				'left' : hooverLeft + 'px',
				'top' : hooverTop + 'px',
				'backgroundColor' : 'rgba(255, 0, 0, 0.2)'
			});
		}
	},
	changeValue : function(item) {
		this.model.set('CONTENT', item.target.textContent);
		
		//TODO splitting token
		/*if (item.target.textContent.indexOf(' ') >= 0) {
			let splitted = item.target.textContent.split(' ');
			
			let textlineString = this.model.collection;
			let textLine = this.model.relatedModel;
			this.render();
		} else {
			this.model.set('CONTENT', item.target.textContent);
		}*/
		
		
		//this.render();
	},
	render : function() {
		this.$el.empty();
		
		this.$el.html(this.model.get('CONTENT'));
		
		var css = {
				'position' : 'absolute',	
				'width' : this.model.get('WIDTH') * editorOptions['zoomFactor'] + 'px',
				'height' : this.model.get('HEIGHT') * editorOptions['zoomFactor'] + 'px',
				'left' : this.model.get('HPOS') * editorOptions['zoomFactor'] + 'px',
				'top' : this.model.get('VPOS') * editorOptions['zoomFactor'] + 'px',
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
			css['font-family'] = 'Arial Narrow';
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
