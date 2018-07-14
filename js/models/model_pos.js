window.POS = Backbone.RelationalModel.extend({
    url:"http://localhost.ontologydriven/api/nlp/tagpos",
    defaults:{
    	"tagged":""
    }
});

window.POSCollection = Backbone.Collection.extend({
    model:POS,
    url:"http://localhost.ontologydriven/api/nlp/tagpos"
});