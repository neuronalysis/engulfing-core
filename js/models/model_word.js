window.Word = Master.extend({
	urlRoot : apiHost + "nlp/words",
	type : "Word",
	
	defaults : {
		"id" : null,
		"name" : "",
		"Language" : null,
		"Lexeme" : null,
		"type" : "",
		"tagBrown" : "",
		"numerus" : "",
		"person" : "",
		"kasus" : "",
		"genus" : "",
		"tempus" : ""
	}, initialize : function() {
	    this.set('Language', Language.findOrCreate({id: Cookie.get('UserLanguageID')}));
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'Language',
		relatedModel : 'Language'
	}, {
		type : Backbone.HasOne,
		key : 'Lexeme',
		relatedModel : 'Lexeme'
	} ]
});

window.WordCollection = MasterCollection.extend({
	model : Word,
	url : apiHost + "nlp/words"
});