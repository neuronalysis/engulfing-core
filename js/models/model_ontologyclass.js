window.OntologyClass = Master.extend({
	urlRoot : kmapiHost + "km/ontologyclasses",
	type : "OntologyClass",

	defaults : {
		"id" : null,
		"name" : "",
		"Ontology" : null,
		"Ressource" : null,
		"isPersistedConcrete" : null,
		"Lexemes" : null,
		"RelationOntologyClassOntologyClasses" : null,
		"RelationOntologyClassOntologyProperties" : null
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'Ontology',
		relatedModel : 'Ontology'
	}, {
		type : Backbone.HasOne,
		key : 'Ressource',
		relatedModel : 'Ressource'
	}, {
		type : Backbone.HasMany,
		key : 'Lexemes',
		relatedModel : 'Lexeme',
		collectionType : 'LexemeCollection'
	}, {
		type : Backbone.HasMany,
		key : 'RelationOntologyClassOntologyClasses',
		relatedModel : 'RelationOntologyClassOntologyClass',
		// includeInJSON: Backbone.Model.prototype.idAttribute,
		collectionType : 'RelationOntologyClassOntologyClassCollection'
	}, {
		type : Backbone.HasMany,
		key : 'RelationOntologyClassOntologyProperties',
		relatedModel : 'RelationOntologyClassOntologyProperty',
		// includeInJSON: Backbone.Model.prototype.idAttribute,
		collectionType : 'RelationOntologyClassOntologyPropertyCollection'

	} ],
	getOntologyPropertyByName : function(name) {
		var relOCOC_Properties = this
				.get('RelationOntologyClassOntologyProperties');
		for (var i = 0; i < relOCOC_Properties.length; i++) {
			if (relOCOC_Properties.models[i].get('OntologyProperty')
					.get('name') === name) {
				return relOCOC_Properties.models[i].get('OntologyProperty');
			}
		}

		return false;
	}
});

window.OntologyClassCollection = MasterCollection.extend({
	model : OntologyClass,
	url : kmapiHost + "km/ontologyclasses"
});

window.OutgoingOntologyClass = OntologyClass.extend({

});
window.IncomingOntologyClass = OntologyClass.extend({

});