window.Teaser = Master.extend({
	urlRoot : apiHost + "teaser",
	
	initialize: function(props){
	    this.urlRoot += "/" + props.ontologyName;
	},
	
	defaults : {
		"id" : null
	},
	relations : [  ]
});
