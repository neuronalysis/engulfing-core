window.NewsTeaserView = Backbone.View.extend({
	//tagName : "ul",
	//className : "media-list",

	initialize : function(options) {
		this.template = _.template(tpl.get('layouts/newsteaser_frontpage'));
    	
		this.options = options;
	},
	render : function() {
		var titleHTML = '';
		titleHTML += '<h2>News</h2>';
		
		this.$el.append(titleHTML);
		
		var teaserModel = this.model;
		
		newsTeaserPromise = teaserModel.fetch({reset: true});
		
		var self = this;
		
		var spinnerHTML = '';
		spinnerHTML += '<div class="spinner-div">' +
	      '<span class="glyphicon glyphicon-refresh spin"></span>';
		spinnerHTML += '</div>';
		
		self.$el.append(spinnerHTML);
		
		//this.$el.append(titleHTML).append(subtitleHTML).append(chartsView.render().$el);
		$.when(newsTeaserPromise).then(function() {
			var data = teaserModel.attributes;
			
			self.$el.html(self.template(data));
		});
		
		
		return this;
	}
});