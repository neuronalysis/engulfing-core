var TextLineView = BaseView.extend({
	initialize : function() {
		this.fontSize = this.model.getFontSize();
		//this.template = _.template(tpl.get('components/editor'));
	},
	render : function() {
		this.$el.empty();
		
		if (this.model.get('STYLEREFS')) {
			var fontId =  this.model.get('STYLEREFS').split(" ")[1];
			
			var textStyle = window['TextStyle'].findOrCreate({
				id : fontId
			});
			
			/*this.$el.css({
				'position'          : 'absolute',
				'font-size'		: textStyle.get('FONTSIZE') + 'px',
				'font-family'	: textStyle.get('FONTFAMILY'),
				'width'         : this.model.get('WIDTH') / 3 + 'px',
				'height'        : this.model.get('HEIGHT') / 3 + 'px',
				'left'          : this.model.get('HPOS') / 3 + 'px',
				'top'           : this.model.get('VPOS') / 3 + 'px'
			    });*/
		}
			
		
		_.each(this.model.get('Strings').models, function(object) {
			var stringView = new StringView({
				model : object,
				parent : this
			});
			$(this.el).append(stringView.render().el);
			
			if (object.get('CONTENT') == 'WIRTSCHAFTSRE6I0NEN') {
				//alert ($(stringView.el).clientWidth);
			}
		}, this);
		
		_.each(this.model.get('SPs').models, function(object) {
			$(this.el).append(new SPView({
				model : object
			}).render().el);
		}, this);
		
		return this;
	}
});
