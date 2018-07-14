window.OntologyRelationType = Master.extend({
	urlRoot : kmapiHost + "km/ontologyrelationtypes",
	type : "OntologyRelationType",
	
	defaults : {
		"id" : null,
		"name" : "",
		"Ontology" : null,
		"Lexemes" : null
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

window.OntologyRelationTypeCollection = MasterCollection.extend({
	model : OntologyRelationType,
	url : kmapiHost + "km/ontologyrelationtypes"
});