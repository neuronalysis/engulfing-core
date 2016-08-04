window.NewsView = Backbone.View.extend({
	tagName : "ul",
	className : "media-list",

	initialize : function(options) {
		this.options = options;
	},
	render : function() {
		if (this.collection.at(0) == null)
			return false;
		
		$(this.el).append('<h2>' + this.options.objectName + '</h2>');
				
		_.each(this.collection.models, function(object) {
			$(this.el).append(new NewsItemView({
				model : object
			}).render().el);
		}, this);
		
		
		return this;
	}
});

var NewsItemView = Backbone.View.extend({
	tagName : "li",
	className : "media",

	initialize : function(options) {
		this.options = options;
	},

	render : function() {
		var newsHTML = '';
		
		var datePublishedAt = new Date(this.model.get('publishedAt'));
		
		newsHTML += '<div class="media-body">' +
	      '<h4 class="media-heading">' + this.model.get('title') + ' (' + datePublishedAt.format() + ')' + '</h4>';
		
		if (this.model.get('header')) newsHTML +=  '<p>' + this.model.get('header') + '</p>'
		
		newsHTML += this.model.get('content');
		newsHTML += '</div>';
		
		this.$el.append(newsHTML);
		
		return this;
	}
	
});