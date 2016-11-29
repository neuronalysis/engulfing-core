window.NewsView = Backbone.View.extend({
	tagName : "ul",
	className : "media-list",

	initialize : function(options) {
		this.options = options;
	},
	render : function() {
		var titleHTML = '';
		titleHTML += '<h2>' + this.options.objectName + '</h2>';
		
		this.$el.append(titleHTML);
		
		var newsCollection = this.collection;
		
		newsPromise = newsCollection.fetch({reset: true});
		
		var self = this;
		
		var spinnerHTML = '';
		spinnerHTML += '<div class="spinner-div">' +
	      '<span class="glyphicon glyphicon-refresh spin"></span>';
		spinnerHTML += '</div>';
		
		self.$el.append(spinnerHTML);
		
		$.when(newsPromise).then(function() {
			self.$("div.spinner-div").remove();

			
			_.each(newsCollection.models, function(object) {
				$(self.el).append(new NewsItemView({
					model : object
				}).render().el);
			}, self);
		});
		
		
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