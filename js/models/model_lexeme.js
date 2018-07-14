window.Lexeme = Master.extend({
	urlRoot : apiHost + "nlp/lexemes",
	type : "Lexeme",
	
	defaults : {
		"id" : null,
		"language" : ""
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'OntologyClass',
		relatedModel : 'OntologyClass'
	}, {
		type : Backbone.HasOne,
		key : 'OntologyRelationType',
		relatedModel : 'OntologyRelationType'
	}, {
		type : Backbone.HasOne,
		key : 'OntologyProperty',
		relatedModel : 'OntologyProperty'
	} ]
});

window.LexemeCollection = MasterCollection.extend({
	model : Lexeme,
	//type : "Lexeme",
	
	url : apiHost + "nlp/lexemes"
});