var SPView = BaseView.extend({
	tagName : 'span',
	
	initialize : function() {
		//this.template = _.template(tpl.get('components/editor'));
	},
	events : {
		"change" : "changeValue"
	},
	changeValue : function(item) {
		if (typeof this.model.attributes.name === 'undefined') {
			this.model.set(item.target.id, item.target.value);
		} else {
			this.model.set('name', item.target.value);
		}
	},
	render : function() {
		this.$el.html(' ');

		this.$el.attr('VPOS', this.model.get('VPOS'));
		this.$el.attr('HPOS', this.model.get('HPOS'));
		this.$el.attr('WIDTH', this.model.get('WIDTH'));

		this.$el.css({
			'position'          : 'absolute',
			'width'         : this.model.get('WIDTH') * editorOptions['zoomFactor'] + 'px',
			'left'          : this.model.get('HPOS') * editorOptions['zoomFactor'] + 'px',
			'top'           : this.model.get('VPOS') * editorOptions['zoomFactor'] + 'px'
		    });
		
		return this;
	}
});
