window.Ontology = Master.extend({
	urlRoot : kmapiHost + "km/ontologies",
	
	defaults : {
		"id" : null,
		"name" : "",
		"isPrivate" : false,
		"isFinal" : false,
		"Owner" : null,
		"OntologyClasses" : null,
		"OntologyProperties" : null
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'Owner',
		relatedModel : 'Owner'
	}, {
		type : Backbone.HasMany,
		key : 'OntologyClasses',
		relatedModel : 'OntologyClass',
		collectionType : 'OntologyClassCollection'
	}, {
		type : Backbone.HasMany,
		key : 'OntologyProperties',
		relatedModel : 'OntologyProperty',
		collectionType : 'OntologyPropertyCollection'
	} ]
});

window.OntologyCollection = MasterCollection.extend({
	model : Ontology,
	url : kmapiHost + "km/ontologies"
});