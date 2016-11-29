window.DataSummaryView = Backbone.View.extend({
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
			newsHTML += '<ul class="list-group">';
			
			newsHTML += '<li class="list-group-item">' +
				  '<span class="badge">' + summary.get('ontologies') + '</span>' +
				  '<a href="./km/ontologies/">Ontologies</a>' +
				  '</li>';

			newsHTML += '<li class="list-group-item">' +
			  '<span class="badge">' + summary.get('ontologyClasses') + '</span>' +
			  '<a href="./km/ontologyclasses/">Classes</a>' +
			  '</li>';

			newsHTML += '<li class="list-group-item">' +
			  '<span class="badge">' + summary.get('ontologyProperties') + '</span>' +
			  '<a href="./km/ontologyproperties/">Properties</a>' +
			  '</li>';

			newsHTML += '<li class="list-group-item">' +
			  '<span class="badge">' + summary.get('ontologyRelationTypes') + '</span>' +
			  '<a href="./km/ontologyrelationtypes/">Relation Types</a>' +
			  '</li>';

			newsHTML += '</ul>';
			
			self.$el.append(newsHTML);
		});
		
		
		return this;
	}
});

window.WikiDataSummaryView = DataSummaryView.extend({
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
			newsHTML += '<ul class="list-group">';
			
			for (var k in summary.get('ontologyClassEntities')){
				newsHTML += '<li class="list-group-item">' +
				  '<span class="badge">' + summary.get('ontologyClassEntities')[k]['count'] + '</span>' +
				  '<a href="./km/ontologyclasses/#' + summary.get('ontologyClassEntities')[k]['classID'] + '/entities' + '">' + getPlural(k) + '</a>' +
				  '</li>';
			};
		
			
			newsHTML += '</ul>';
			
			self.$el.append(newsHTML);
		});
		
		return this;
	}
});