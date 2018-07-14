window.OntologyProperty = Master.extend({
	urlRoot : kmapiHost + "km/ontologyproperties",
	type : "OntologyProperty",
	
	defaults : {
		"id" : null,
		"name" : "",
		"Type" : null,
		"Ontology" : null,
		"Lexemes" : null,
		"isIdentifier" : false,
		"isMandatory" : false,
		"length" : null,
		"validationRegularExpression" : "",
		"defaultValue" : null
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'Ontology',
		relatedModel : 'Ontology'
	},{
		type : Backbone.HasOne,
		key : 'Type',
		relatedModel : 'Type'
	}, {
		type : Backbone.HasMany,
		key : 'Lexemes',
		relatedModel : 'Lexeme',
		collectionType : 'LexemeCollection'
	} ]
});

window.OntologyPropertyCollection = MasterCollection.extend({
	model : OntologyProperty,
	url : kmapiHost + "km/ontologyproperties"
});

window.Type = Backbone.RelationalModel.extend({
	type : "Type",
	defaults : {
		"id" : null,
		"text" : ""
	},
	enumeration : [ {
		id : "0",
		text : "Text"
	}, {
		id : "1",
		text : "Date"
	}, {
		id : "2",
		text : "Number"
	}, {
		id : "3",
		text : "Boolean"
	} ]
});