window.DataSummaryView = Backbone.View.extend({
	tagName : "ul",
	className : "media-list",

	initialize : function(options) {
		this.options = options;
	},
	
	render : function() {
		$(this.el).append('<h2>' + this.options.objectName + '</h2>');
				
		var newsHTML = '';
		newsHTML += '<ul class="list-group">';
		
		newsHTML += '<li class="list-group-item">' +
			  '<span class="badge">' + this.options.summaryData.get('ontologies') + '</span>' +
			  '<a href="./km/ontologies/">Ontologies</a>' +
			  '</li>';

		newsHTML += '<li class="list-group-item">' +
		  '<span class="badge">' + this.options.summaryData.get('ontologyClasses') + '</span>' +
		  '<a href="./km/ontologyclasses/">Classes</a>' +
		  '</li>';

		newsHTML += '<li class="list-group-item">' +
		  '<span class="badge">' + this.options.summaryData.get('ontologyProperties') + '</span>' +
		  '<a href="./km/ontologyproperties/">Properties</a>' +
		  '</li>';

		newsHTML += '<li class="list-group-item">' +
		  '<span class="badge">' + this.options.summaryData.get('ontologyRelationTypes') + '</span>' +
		  '<a href="./km/ontologyrelationtypes/">Relation Types</a>' +
		  '</li>';

		newsHTML += '</ul>';
		
		this.$el.append(newsHTML);
		
		
		return this;
	}
});

window.WikiDataSummaryView = DataSummaryView.extend({
	render : function() {
		$(this.el).append('<h2>' + this.options.objectName + '</h2>');
				
		var newsHTML = '';
		newsHTML += '<ul class="list-group">';
		
		
		for (var k in this.options.summaryData.get('ontologyClassEntities')){
			newsHTML += '<li class="list-group-item">' +
			  '<span class="badge">' + this.options.summaryData.get('ontologyClassEntities')[k]['count'] + '</span>' +
			  '<a href="./km/ontologyclasses/#' + this.options.summaryData.get('ontologyClassEntities')[k]['classID'] + '/entities' + '">' + getPlural(k) + '</a>' +
			  '</li>';
		};
		
		
			
		newsHTML += '</ul>';
		
		
		this.$el.append(newsHTML);
		
		
		return this;
	}
});