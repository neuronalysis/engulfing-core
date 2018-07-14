window.OntologyEntity = Backbone.Model.extend({
	urlRoot : kmapiHost + "km/Ontologyentitys",

	defaults : {
		"id" : null
	}
});

window.OntologyEntityCollection = Backbone.Collection.extend({
	model : OntologyEntity,
	url : kmapiHost + "km/Ontologyentitys"
});