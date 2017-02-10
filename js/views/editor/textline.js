var TextLineView = BaseView.extend({
	initialize : function() {
		//this.template = _.template(tpl.get('components/editor'));
	},
	render : function() {
		this.$el.empty();
		
		if (this.model.get('stylerefs')) {
			var fontId =  this.model.get('stylerefs').split(" ")[1];
			
			var textStyle = window['TextStyle'].findOrCreate({
				id : fontId
			});
			
			this.$el.css({
				'position'          : 'absolute',
				'font-size'		: textStyle.get('fontsize') + 'px',
				'font-family'	: textStyle.get('fontfamily'),
				'width'         : this.model.get('width') / 3 + 'px',
				'height'        : this.model.get('height') / 3 + 'px',
				'left'          : this.model.get('hpos') / 3 + 'px',
				'top'           : this.model.get('vpos') / 3 + 'px'
			    });
		}
			
		
		
		
		_.each(this.model.get('Strings').models, function(object) {
			$(this.el).append(new StringView({
				model : object
			}).render().el);
		}, this);
		
		_.each(this.model.get('SPs').models, function(object) {
			$(this.el).append(new SPView({
				model : object
			}).render().el);
		}, this);
		
		return this;
	}
});
