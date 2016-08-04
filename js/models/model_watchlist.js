window.Watchlist = Master.extend({
	urlRoot : apiHost + "observation/watchlists",
	
	defaults : {
		"id" : null,
		"name" : "",
		"Owner" : null,
		"WatchlistItems" : null
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'Owner',
		relatedModel : 'Watchlist'
	}, {
		type : Backbone.HasMany,
		key : 'WatchlistItems',
		relatedModel : 'WatchlistItem',
		collectionType : 'WatchlistItemCollection'
	} ]
});

window.WatchlistCollection = MasterCollection.extend({
	model : Watchlist,
	url : apiHost
});

window.WatchlistItem = Master.extend({
	urlRoot : apiHost + "observation/watchlistitems",
	
	defaults : {
		"id" : null,
		"OntologyClass" : null,
		"Entity" : null
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'OntologyClass',
		relatedModel : 'OntologyClass'
	},
	{
		type : Backbone.HasOne,
		key : 'Entity',
		relatedModel : 'Entity'
	}]
});

window.WatchlistItemCollection = MasterCollection.extend({
	model : WatchlistItem,
	url : apiHost
});

window.Entity = Master.extend({
	defaults : {
		"id" : null
	}
});