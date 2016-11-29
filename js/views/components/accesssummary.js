window.AccessSummaryView = Backbone.View.extend({
	tagName : "ul",
	className : "media-list",

	initialize : function(options) {
		this.options = options;
	},
	
	render : function() {
		var titleHTML = '';
		titleHTML += '<h2>' + this.options.objectName + '</h2>';
		
		this.$el.append(titleHTML);
		
		var summary = this.options.summaryData;
		
		summaryPromise = summary.fetch({reset: true});
		
		var self = this;
		
		var spinnerHTML = '';
		spinnerHTML += '<div class="spinner-div">' +
	      '<span class="glyphicon glyphicon-refresh spin"></span>';
		spinnerHTML += '</div>';
		
		self.$el.append(spinnerHTML);
		
		$.when(summaryPromise).then(function() {
			self.$("div.spinner-div").remove();

			var newsHTML = '';
			
			_.each(summary.get('AccessDestinations').models, function(object) {
				newsHTML += '<li class="list-group-item">' +
				  '<span class="badge">' + object.get('visits') + '</span>' +
				  '<a href="' + object.get('url') + '">' + object.get('title') + '</a>' +
				  '</li>';
			}, self);
			
			newsHTML += '</ul>';
			
			self.$el.append(newsHTML);
		});
		
		
		return this;
	}
});