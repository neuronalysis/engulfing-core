window.Service = Master.extend({
	urlRoot : kmapiHost,

	initialize: function(props){
		var parameterString = $.param( props.parameters );

	    this.urlRoot += props.ontology + "/" + props.ressource + "?" + parameterString;
	},
	defaults : {
		"ontology" : null,
		"ressource" : null
	}
});

window.ServiceCollection = MasterCollection.extend({
	model : Service,
	url : kmapiHost
});