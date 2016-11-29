var HighChartsView = InputView.extend({
	initialize : function(options) {
		this.template = _.template(tpl.get('components/input_highcharts'));
		this.withLabel = true;
		this.withCell = false;
		
		if (options.observationsLimit) {
			this.observationsLimit = options.observationsLimit;
		}
		
	},
	events : {
		
	},
	changeValue : function(item) {
	},
	render : function() {
		var model_observations = this.model.get(this.field);
		
		if (model_observations !== undefined) {
			var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "label": this.label, "field_value": model_observations.getString(), "withCell" : this.withCell, "withLabel" : this.withLabel, "chart_height" : "250px"};
		} else {
			var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "label": this.label, "field_value": null, "withCell" : this.withCell, "withLabel" : this.withLabel, "chart_height" : "100px"};
		}
		
		this.$el.html(this.template(data));
		
		var spinnerHTML = '';
		spinnerHTML += '<div class="spinner-div" style="vertical-align: top;">' +
	      '<span class="glyphicon glyphicon-refresh spin" style="vertical-align: top;"></span>';
		spinnerHTML += '</div>';
		
		this.$(".form-group").append(spinnerHTML);
		
		if (model_observations === undefined || model_observations.length == 0) {
			model_observations = new ObservationCollection();
			
			if (this.model.isConcrete()) {
				if (this.model.type === "Indicator") {
					model_observations.type = "IndicatorObservations";
					model_observations.url = apiHost + "economics/indicators/" + this.model.id + "/observations";
				} else if (this.model.type === "Instrument") {
					model_observations.type = "InstrumentObservations";
					model_observations.url = apiHost + "economics/instruments/" + this.model.id + "/observations";
				}
			} else {
				if (this.model.get('OntologyClass').get('name') === "Indicator") {
					model_observations.type = "IndicatorObservations";
					model_observations.url = apiHost + "economics/indicators/" + this.model.id + "/observations";
					
				} else if (this.model.get('OntologyClass').get('name') === "Instrument") {
					model_observations.type = "InstrumentObservations";
					model_observations.url = apiHost + "economics/instruments/" + this.model.id + "/observations";
					
				} else {
					model_observations.type = "IndicatorObservations";
					model_observations.url = apiHost + "economics/indicators/" + this.model.id + "/observations";
					
				}
			}
			
			if (this.observationsLimit) {
				model_observations.url += "?limit=" + this.observationsLimit;
			}
			
			model_observations.fetch({
				success : function(model_observations) {
					this.$(".spinner-div").remove();
					
					var series_data = new Array;
					
					for (var i = 0; i < model_observations.models.length; i++) {
						var parts = model_observations.models[i].get('date').split(' ')[0].split('-');
						
						series_data.push([
			                  Date.UTC(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2])),
			                  parseFloat(model_observations.models[i].get('value'))]
						);
					}
					
					
					this.$("#" + getPlural(model_observations.type)).highcharts({
				        chart: {
				            zoomType: 'x',
				            height: 200
				        },
				        title: {
				            text: ''
				        },
				        subtitle: {
				            text: document.ontouchstart === undefined ?
				                    '' :
				                    ''
				        },
				        xAxis: {
				            type: 'datetime',
				            minRange: 14 * 24 * 3600000 // fourteen days
				        },
				        yAxis: {
				            title: {
				                text: ''
				            }
				        },
				        legend: {
				            enabled: false
				        },
				        plotOptions: {
				            area: {
				                fillColor: {
				                    linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1},
				                    stops: [
				                        [0, Highcharts.getOptions().colors[0]],
				                        [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
				                    ]
				                },
				                marker: {
				                    radius: 2
				                },
				                lineWidth: 1,
				                states: {
				                    hover: {
				                        lineWidth: 1
				                    }
				                },
				                threshold: null
				            }
				        },
				        series: [{
				            type: 'area',
				            name: '',
				            pointInterval: 24 * 3600 * 1000,
				            pointStart: Date.UTC(1906, 0, 1),
				            data: series_data
				        }]
			        });
				}
			});
		}
		
		return this;
	}
});
		