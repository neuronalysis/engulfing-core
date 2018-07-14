window.RelationOntologyClassOntologyProperty = Master.extend({
	urlRoot : kmapiHost + "km/relationontologyclassontologyproperties",

	defaults : {
		"id" : null
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'OntologyClass',
		relatedModel : 'OntologyClass'
	}, {
		type : Backbone.HasOne,
		key : 'OntologyProperty',
		relatedModel : 'OntologyProperty'
	} ]

});

window.RelationOntologyClassOntologyPropertyCollection = MasterCollection
		.extend({
			model : RelationOntologyClassOntologyProperty,
			url : kmapiHost + "km/relationontologyclassontologyproperties"
		});