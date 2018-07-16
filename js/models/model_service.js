window.Service = Master.extend({
	urlRoot : kmapiHost,

	initialize: function(props){
		var parameterString = $.param( props.parameters );

	    this.urlRoot += props.ontology + "/" + props.resource + "?" + parameterString;
	},
	defaults : {
		"ontology" : null,
		"resource" : null
	}
});

window.ServiceCollection = MasterCollection.extend({
	model : Service,
	url : kmapiHost
});