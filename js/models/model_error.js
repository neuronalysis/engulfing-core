window.Error = Backbone.RelationalModel.extend({
    defaults:{
    	"message":""
    }
});

window.ErrorCollection = Backbone.Collection.extend({
    model:Error,
});