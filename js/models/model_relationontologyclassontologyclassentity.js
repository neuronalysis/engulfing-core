window.RelationOntologyClassOntologyClassEntity = Master.extend({
	urlRoot : kmapiHost + "km/relationontologyclassontologyclassentities",
	type : "RelationOntologyClassOntologyClassEntity",

	defaults : {
		"id" : null
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'OutgoingOntologyClassEntity',
		relatedModel : 'OutgoingOntologyClassEntity'
	}, {
		type : Backbone.HasOne,
		key : 'IncomingOntologyClassEntity',
		relatedModel : 'IncomingOntologyClassEntity'
	}, {
		type : Backbone.HasOne,
		key : 'OntologyRelationType',
		relatedModel : 'OntologyRelationType'
	} ]

});

window.RelationOntologyClassOntologyClassEntityCollection = MasterCollection
		.extend({
			model : RelationOntologyClassOntologyClassEntity,
			url : kmapiHost + "km/relationontologyclassontologyclassentities"
		});