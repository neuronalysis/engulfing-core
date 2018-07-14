window.RelationOntologyClassOntologyClass = Master.extend({
	urlRoot : kmapiHost + "km/relationontologyclassontologyclasses",

	defaults : {
		"id" : null
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'OutgoingOntologyClass',
		relatedModel : 'OutgoingOntologyClass'
	}, {
		type : Backbone.HasOne,
		key : 'OntologyRelationType',
		relatedModel : 'OntologyRelationType'
	}, {
		type : Backbone.HasOne,
		key : 'IncomingOntologyClass',
		relatedModel : 'IncomingOntologyClass'
	} ]

});

window.RelationOntologyClassOntologyClassCollection = MasterCollection
		.extend({
			model : RelationOntologyClassOntologyClass,
			url : kmapiHost + "km/relationontologyclassontologyclasses"
		});