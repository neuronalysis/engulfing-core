var ScanImageView = BaseView.extend({
	initialize : function() {
	},
	events : {
		"change" : "changeValue"
	},
	render : function() {
		this.$el.css({
			'position'          : 'absolute',
			'left'          : this.model.get('width')
			});
		
		this.$el.html('<image src="' + this.model.get('filePath') + '" height="' + this.model.get('height') + '"></image>');

		return this;
	}
});
