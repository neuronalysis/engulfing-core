window.Corpus = Backbone.RelationalModel.extend({
	urlRoot : apiHost + "nlp/corpora",

	defaults : {
		"id" : null,
		"text" : "",
		"annotation" : ""
	}
});

window.CorpusCollection = Backbone.PageableCollection.extend({
	model : Corpus,
	url : apiHost + "nlp/corpora",
	state : {
		pageSize : 15
	},
	// get the state from Github's search API result
    parseState: function (resp, queryParams, state, options) {
      return {totalRecords: resp.total_count};
    },

    // get the actual records
    parseRecords: function (resp, options) {
      return resp.items;
    }
});