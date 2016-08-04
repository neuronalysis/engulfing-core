window.News = Master.extend({
	urlRoot : apiHost + "news",
	defaults : {
		"id" : null,
		"title" : "",
		"publishedAt" : "",
		"header" : "",
		"content" : ""
	}
});

window.NewsCollection = MasterCollection.extend({
	model : News,
	url : apiHost + "news",
	
	initialize: function(props){
	    this.url += props.topic;
	}
	
});