window.OntologyRelationTypeEntity = Master.extend({
	urlRoot : kmapiHost + "km/ontologyrelationtypeentities",
	type : "OntologyRelationTypeEntity",
	
	defaults : {
		"id" : null,
		"name" : ""
	},

	relations : [  {
		type : Backbone.HasOne,
		key : 'Ontology',
		relatedModel : 'Ontology'
	}, {
		type : Backbone.HasMany,
		key : 'Lexemes',
		relatedModel : 'Lexeme',
		collectionType : 'LexemeCollection'
	} ]
});

window.OntologyRelationTypeEntityCollection = MasterCollection.extend({
	model : OntologyRelationTypeEntity,
	url : kmapiHost + "km/ontologyrelationtypeentities"
});