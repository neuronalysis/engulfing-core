window.Lexeme = Master.extend({
	urlRoot : apiHost + "nlp/lexemes",

	defaults : {
		"id" : null,
		"name" : "",
		"Language" : null,
		"Words" : null,
		"OntologyClass" : null,
		"OntologyRelationType" : null,
		"OntologyProperty" : null,
		"OntologyClassEntity" : null,
		"OntologyPropertyEntity" : null
	}, initialize : function() {
	    this.set('Language', Language.findOrCreate({id: Cookie.get('UserLanguageID')}));
	}, relations : [ {
		type : Backbone.HasOne,
		key : 'Language',
		relatedModel : 'Language'
	}, {
		type : Backbone.HasMany,
		key : 'Words',
		relatedModel : 'Word',
		collectionType : 'WordCollection'
	}, {
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
	}, {
		type : Backbone.HasOne,
		key : 'OntologyClassEntity',
		relatedModel : 'OntologyClassEntity'
	}, {
		type : Backbone.HasOne,
		key : 'OntologyPropertyEntity',
		relatedModel : 'OntologyPropertyEntity'
	} ]
});

window.LexemeCollection = MasterCollection.extend({
	model : Lexeme,
	url : apiHost + "nlp/lexemes"
});


window.Language = Master.extend({
	type : "Language",
	defaults : {
		"id" : null,
		"isoCode" : "",
		"name" : ""
	},
	enumeration : [ {
		id : 0,
		text : "English"
	}, {
		id : 1,
		text : "German"
	} ]
});