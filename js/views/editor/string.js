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
		this.$el.attr('VPOS', this.model.get('VPOS'));
		this.$el.attr('HPOS', this.model.get('HPOS'));
		
		var css = {
				'position' : 'absolute',	
				'width' : this.model.get('WIDTH') * editorOptions['zoomFactor'] + 'px',
				'height' : (this.parent.model.get('HEIGHT') * editorOptions['zoomFactor'] + 3) + 'px',
				'left' : (this.model.get('HPOS') - this.parent.model.get('HPOS')) * editorOptions['zoomFactor'] + 'px',
				'top' : '4px',
				
				'white-space' : 'nowrap',
				'display':'inline-block'
			};
		
		if (accessMode == "edit") {
			this.$el.attr('contentEditable', true);
			
			this.$el.append('<div style="position: absolute; top: 1px; left: 0px; outline: gainsboro solid 1px; width: ' + this.model.get('WIDTH') * editorOptions['zoomFactor'] + 'px' + '; height: ' + (this.parent.model.get('HEIGHT') * editorOptions['zoomFactor'] + 1) + 'px' + '; z-index: -1;"></div>')
		} else {
			this.$el.attr('contentEditable', false);
		}
		
		css['font-family'] = this.parent.fontCSS['font-family'];
		css['font-size'] = this.parent.fontCSS['font-size'];
		
		this.$el.css(css);
		
		return this;
	}
});
