var TextBlockView = BaseView.extend({
	initialize : function() {
		//this.template = _.template(tpl.get('components/editor'));
	},
	render : function() {
		var fontId =  this.model.get('stylerefs').split(" ")[1];
		
		var textStyle = window['TextStyle'].findOrCreate({
			id : fontId
		});
		
		this.$el.empty();
		
		this.$el.css({
			'position'          : 'absolute',
			'font-size'		: textStyle.get('fontsize'),
			'font-family'	: textStyle.get('fontfamily'),
			'width'         : this.model.get('width') / 3 + 'px',
			'height'        : this.model.get('height') / 3 + 'px',
			'left'          : this.model.get('hpos') / 3 + 'px',
			'top'           : this.model.get('vpos') / 3 + 'px'
		    });
		
		
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
