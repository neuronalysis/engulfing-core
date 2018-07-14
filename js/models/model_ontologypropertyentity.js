window.OntologyPropertyEntity = Master.extend({
	urlRoot : kmapiHost + "km/ontologypropertyentities",
	type : "OntologyPropertyEntity",
	
	defaults : {
		"id" : null,
		"name" : ""
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'OntologyProperty',
		relatedModel : 'OntologyProperty'
	}, {
		type : Backbone.HasMany,
		key : 'Lexemes',
		relatedModel : 'Lexeme',
		collectionType : 'LexemeCollection'
	} ]
});

window.OntologyPropertyEntityCollection = MasterCollection.extend({
	model : OntologyPropertyEntity,
	url : kmapiHost + "km/ontologypropertyentities"
});