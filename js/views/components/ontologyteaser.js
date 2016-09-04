window.OntologyTeaserView = Backbone.View.extend({
	//tagName : "ul",
	//className : "media-list",

	initialize : function(options) {
		this.options = options;
	},
	
	render : function() {
		var titleHTML = '';
		titleHTML += '<h2>Economics</h2>';
		
		var subtitleHTML = '';
		subtitleHTML += '<div class="media-body">' +
	      '<h4 class="media-heading">' + '<a href="' + this.model.getUrl() + '">' + this.model.get('name') + '</a>' + ' last updated on ' + new Date(this.model.get('lastIndicatorObservationDate')).format() + '</h4>';
		
		subtitleHTML += '</div>';
		
		
		
		var chartsView = new HighChartsView({model : this.model, observationsLimit : 60});
		chartsView.withLabel = false;
		chartsView.field = 'IndicatorObservations';
		
		
		//teaserHTML += economicsView.render().el.html;
		
		//this.$el.html(this.template(data));
    	
		
		//$(this.el).append('<h2>' + this.options.objectName + '</h2>');
				
		/*_.each(this.options.summaryData.get('AccessDestinations').models, function(object) {
			newsHTML += '<li class="list-group-item">' +
			  '<span class="badge">' + object.get('visits') + '</span>' +
			  '<a href="' + object.get('url') + '">' + object.get('title') + '</a>' +
			  '</li>';
		}, this);
		*/
		
		
		this.$el.append(titleHTML).append(subtitleHTML).append(chartsView.render().$el);
		
		
		return this;
	}
});

window.FinancialMarketsTeaserView = Backbone.View.extend({
	//tagName : "ul",
	//className : "media-list",

	initialize : function(options) {
		this.options = options;
	},
	
	render : function() {
		var titleHTML = '';
		titleHTML += '<h2>Financial Markets</h2>';
		
		var subtitleHTML = '';
		subtitleHTML += '<div class="media-body">' +
	      '<h4 class="media-heading">' + '<a href="' + this.model.getUrl() + '">' + this.model.get('name') + '</a>' + ' last updated on ' + new Date(this.model.get('lastInstrumentObservationDate')).format() + '</h4>';
		
		subtitleHTML += '</div>';
		
		
		
		var chartsView = new HighChartsView({model : this.model, observationsLimit : 250});
		chartsView.withLabel = false;
		chartsView.field = 'InstrumentObservations';
		
		//teaserHTML += economicsView.render().el.html;
		
		//this.$el.html(this.template(data));
    	
		
		//$(this.el).append('<h2>' + this.options.objectName + '</h2>');
				
		/*_.each(this.options.summaryData.get('AccessDestinations').models, function(object) {
			newsHTML += '<li class="list-group-item">' +
			  '<span class="badge">' + object.get('visits') + '</span>' +
			  '<a href="' + object.get('url') + '">' + object.get('title') + '</a>' +
			  '</li>';
		}, this);
		*/
		
		
		this.$el.append(titleHTML).append(subtitleHTML).append(chartsView.render().$el);
		
		
		return this;
	}
});