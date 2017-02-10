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
		
		this.$el.css({
			'position'          : 'absolute',
			'width'         : this.model.get('width') / 3 + 'px',
			'left'          : this.model.get('hpos') / 3 + 'px',
			'top'           : this.model.get('vpos') / 3 + 'px'
		    });
		
		return this;
	}
});
