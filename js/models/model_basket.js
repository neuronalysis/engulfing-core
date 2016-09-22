window.Basket = Master.extend({
	urlRoot : apiHost + "ecommerce/baskets",
	
	defaults : {
		"id" : null,
		"name" : "",
		"Owner" : null,
		"Positions" : null
	},
	relations : [ {
		type : Backbone.HasMany,
		key : 'Positions',
		relatedModel : 'Position',
		collectionType : 'PositionCollection'
	} ],
	addPosition : function(object) {
		var ontologyClass = OntologyClass.findOrCreate({name: object.type});
		
		var position = Position.findOrCreate({
			"amount" : 1,
			"productName" : object.type,
			"productTypeName" : object.get('name')
		});
		
		this.get('Positions').add(position);
	}
});

window.BasketCollection = MasterCollection.extend({
	model : Basket,
	url : apiHost
});

window.Position = Master.extend({
	urlRoot : apiHost + "ecommerce/positions",
	
	defaults : {
		"id" : null,
		"amount" : null,
		"productName" : "",
		"productTypeName" : ""
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
	}],

    parse : function(response){
        return response.Positions; 
    }
});

window.PositionCollection = MasterCollection.extend({
	model : Position,
	url : apiHost
});