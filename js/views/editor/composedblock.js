var ComposedBlockView = BaseView.extend({
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
		this.$el.empty();
		
		if (typeof this.model !== 'undefined') {
			_.each(this.model.get('ComposedBlocks').models, function(object) {
				$(this.el).append(new ComposedBlockView({
					model : object
				}).render().el);
			}, this);
			
			_.each(this.model.get('TextBlocks').models, function(object) {
				$(this.el).append(new TextBlockView({
					model : object,
					parent : this
				}).render().el);
			}, this);
		}
		
		return this;
	}
});
