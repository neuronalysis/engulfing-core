window.OntologyTeaserView = Backbone.View.extend({
	//tagName : "ul",
	//className : "media-list",

	initialize : function(options) {
		this.options = options;
	},
	
	render : function() {
		var titleHTML = '';
		titleHTML += '<h2>Economics</h2>';
		
		this.$el.append(titleHTML);
		
		var teaserModel = this.model;
		
		teaserModelPromise = teaserModel.fetch({reset: true});
		
		var self = this;
		
		var spinnerHTML = '';
		spinnerHTML += '<div class="economics-spinner-div">' +
	      '<span class="glyphicon glyphicon-refresh spin"></span>';
		spinnerHTML += '</div>';
		
		self.$el.append(spinnerHTML);
		
		//this.$el.append(titleHTML).append(subtitleHTML).append(chartsView.render().$el);
		$.when(teaserModelPromise).then(function() {
			self.$("div.economics-spinner-div").replaceWith(
					'<h4 class="media-heading">' + '<a href="' + teaserModel.getUrl() + '">' + teaserModel.get('name') + '</a>' + ' last updated on ' + new Date(teaserModel.get('lastIndicatorObservationDate')).format() + '</h4>'
					);
			
			var chartsView = new HighChartsView({model : teaserModel, observationsLimit : 60});
			chartsView.withLabel = false;
			chartsView.field = 'IndicatorObservations';
			
			self.$el.append(chartsView.render().$el);
		});
		

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
		
		this.$el.append(titleHTML);
		
		var teaserModel = this.model;
		
		financialmarketsTeaserPromise = teaserModel.fetch({reset: true});
		
		var self = this;
		
		var spinnerHTML = '';
		spinnerHTML += '<div class="spinner-div">' +
	      '<span class="glyphicon glyphicon-refresh spin"></span>';
		spinnerHTML += '</div>';
		
		self.$el.append(spinnerHTML);
		
		//this.$el.append(titleHTML).append(subtitleHTML).append(chartsView.render().$el);
		$.when(financialmarketsTeaserPromise).then(function() {
			self.$("div.spinner-div").replaceWith(
					'<h4 class="media-heading">' + '<a href="' + teaserModel.getUrl() + '">' + teaserModel.get('name') + '</a>' + ' last updated on ' + new Date(teaserModel.get('lastInstrumentObservationDate')).format() + '</h4>'
					);
			
			var chartsView = new HighChartsView({model : teaserModel, observationsLimit : 250});
			chartsView.withLabel = false;
			chartsView.field = 'InstrumentObservations';
			
			self.$el.append(chartsView.render().$el);
		});
		
		
		return this;
	}
});