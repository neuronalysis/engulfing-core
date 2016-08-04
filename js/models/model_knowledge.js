window.Knowledge = Master.extend({
	url : apiHost + "extraction/knowledge",
	defaults : {
		"id": null
	},
	relations : [ {
		type : Backbone.HasMany,
		key : 'fragments',
		relatedModel : 'Fragment',
		// includeInJSON: Backbone.Model.prototype.idAttribute,
		collectionType : 'FragmentCollection'
	} ]
});

window.KnowledgeCollection = MasterCollection.extend({
	model : Knowledge,
	url : apiHost + "extraction/knowledge"
});