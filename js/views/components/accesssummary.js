window.AccessSummaryView = Backbone.View.extend({
	tagName : "ul",
	className : "media-list",

	initialize : function(options) {
		this.options = options;
	},
	
	render : function() {
		if (this.options.summaryData.get('AccessDestinations').at(0) == null)
			return false;
		
		
		var newsHTML = '';
		newsHTML += '<ul class="list-group">';
		
		
		$(this.el).append('<h2>' + this.options.objectName + '</h2>');
				
		_.each(this.options.summaryData.get('AccessDestinations').models, function(object) {
			newsHTML += '<li class="list-group-item">' +
			  '<span class="badge">' + object.get('visits') + '</span>' +
			  '<a href="' + object.get('url') + '">' + object.get('title') + '</a>' +
			  '</li>';
		}, this);
		
		newsHTML += '</ul>';
		
		this.$el.append(newsHTML);
		
		
		return this;
	}
});