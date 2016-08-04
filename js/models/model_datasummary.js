window.DataSummary = Master.extend({
	urlRoot : apiHost + "edi/datasummary",
	
	initialize: function(props){
	    this.urlRoot += props.topic;
	},
	
	defaults : {
		"id" : null,
		"ontologies" : "",
		"ontologyClasses" : "",
		"ontologyProperties" : "",
		"ontologyRelationTypes" : "",
		"facts" : ""
	}
});
