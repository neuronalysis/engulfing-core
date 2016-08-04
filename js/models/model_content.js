window.Content = Master.extend({
	urlRoot : kmapiHost + "web/contents",
	
	defaults : {
		"ontology" : null,
		"ressource" : null
	}
});

window.ContentCollection = MasterCollection.extend({
	urlRoot : kmapiHost + "web/contents",
	model : Content
});