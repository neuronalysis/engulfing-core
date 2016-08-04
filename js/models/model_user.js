window.User = Master.extend({
	urlRoot : apiHost + "authentication/users",

	defaults : {
		"id" : null,
		"name" : "",
		"password" : "",
		"eMail" : "",
		"Language" : null,
		"Role" : null,
		"birthDate" : null,
		"Watchlist" : null
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'Language',
		relatedModel : 'Language'
	}, {
		type : Backbone.HasOne,
		key : 'Role',
		relatedModel : 'Role'
	},
	{
		type : Backbone.HasOne,
		key : 'Watchlist',
		relatedModel : 'Watchlist'
	}]
});

window.UserCollection = MasterCollection.extend({
	model : User,
	url : apiHost + "authentication/users"
});

window.Language = Backbone.RelationalModel.extend({
	type : "Language",
	defaults : {
		"id" : null,
		"isoCode" : "",
		"name" : ""
	},
	enumeration : [ {
		id : "0",
		text : "English"
	}, {
		id : "1",
		text : "German"
	} ]
});
window.Owner = User.extend({
	
});