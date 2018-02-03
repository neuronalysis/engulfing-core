var TextBlockView = BaseView.extend({
	tagName : 'textblock',
	
	initialize : function() {
	},
	render : function() {
		var fontId =  this.model.get('STYLEREFS').split(" ")[1];
		
		var textStyle = window['TextStyle'].findOrCreate({
			id : fontId
		});
		
		this.$el.empty();

		if (typeof this.model !== 'undefined') {
			_.each(this.model.get('TextLines').models, function(object) {
				$(this.el).append(new TextLineView({
					model : object
				}).render().el);
			}, this);
		}
		
		return this;
	}
});
