window.AccessSummary = Master.extend({
	urlRoot : apiHost + "monitoring/accesssummary",
	
	initialize: function(props){
	    this.urlRoot += props.topic;
	},
	
	defaults : {
		"id" : null,
		"AccessDestinations" : ""
	},
	relations : [ {
		type : Backbone.HasMany,
		key : 'AccessDestinations',
		relatedModel : 'AccessDestination',
		collectionType : 'AccessDestinationCollection'
	} ]
});

window.AccessDestination = Master.extend({
	defaults : {
		"url" : "",
		"title" : "",
		"visits" : ""
	}
});

window.AccessDestinationCollection = MasterCollection.extend({
	model : AccessDestination
});