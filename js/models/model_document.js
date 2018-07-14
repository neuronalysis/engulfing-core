window.Document = Master.extend({
	urlRoot : apiHost + "ocr/documents",

	defaults : {
		"id" : null,
		
		"Pages" : null
	}, relations : [ {
		type : Backbone.HasMany,
		key : 'Pages',
		relatedModel : 'Page',
		collectionType : 'PageCollection'
	} ]
});

window.DocumentCollection = MasterCollection.extend({
	model : Document,
	url : apiHost + "ocr/documents"
});