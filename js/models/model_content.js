window.Content = Master.extend({
	urlRoot : kmapiHost + "web/contents",
	
	defaults : {
		"ontology" : null,
		"resource" : null
	}
});

window.ContentCollection = MasterCollection.extend({
	urlRoot : kmapiHost + "web/contents",
	model : Content
});