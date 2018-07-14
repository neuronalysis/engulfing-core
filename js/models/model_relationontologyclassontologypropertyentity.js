window.RelationOntologyClassOntologyPropertyEntity = Master.extend({
	urlRoot : kmapiHost + "km/relationontologyclassontologypropertyentities",
	type : "RelationOntologyClassOntologyPropertyEntity",

	defaults : {
		"id" : null
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'OntologyClassEntity',
		relatedModel : 'OntologyClassEntity'
	}, {
		type : Backbone.HasOne,
		key : 'OntologyPropertyEntity',
		relatedModel : 'OntologyPropertyEntity'
	} ]

});

window.RelationOntologyClassOntologyPropertyEntityCollection = MasterCollection
		.extend({
			model : RelationOntologyClassOntologyPropertyEntity,
			url : kmapiHost + "km/relationontologyclassontologypropertyentities"
		});