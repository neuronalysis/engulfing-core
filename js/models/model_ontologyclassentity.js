window.OntologyClassEntity = Master.extend({
	urlRoot : kmapiHost + "km/ontologyclassentities",
	type : "OntologyClassEntity",
	
	defaults : {
		"id" : null,
		"OntologyClass" : null
	},
	relations : [ {
		type : Backbone.HasOne,
		key : 'OntologyClass',
		relatedModel : 'OntologyClass'
	}, {
		type : Backbone.HasMany,
		key : 'Lexemes',
		relatedModel : 'Lexeme',
		collectionType : 'LexemeCollection'
	}, {
		type : Backbone.HasMany,
		key : 'RelationOntologyClassOntologyClassEntities',
		relatedModel : 'RelationOntologyClassOntologyClassEntity',
		// includeInJSON: Backbone.Model.prototype.idAttribute,
		collectionType : 'RelationOntologyClassOntologyClassEntityCollection'
	}, {
		type : Backbone.HasMany,
		key : 'RelationOntologyClassOntologyPropertyEntities',
		relatedModel : 'RelationOntologyClassOntologyPropertyEntity',
		// includeInJSON: Backbone.Model.prototype.idAttribute,
		collectionType : 'RelationOntologyClassOntologyPropertyEntityCollection'

	}, {
		type : Backbone.HasOne,
		key : 'Resource',
		relatedModel : 'Resource'
	} ],
    getNameProperty : function() {
    	return this.getPropertyEntityByName('name');
	},
	getOntologyClassEntityByName : function(name) {
		for (var i=0; i < this.get('RelationOntologyClassOntologyClassEntities').models.length; i++) {
			if (this.get('RelationOntologyClassOntologyClassEntities').models[i].get('IncomingOntologyClassEntity').get('OntologyClass').get('name') === name) {
				return this.get('RelationOntologyClassOntologyClassEntities').models[i].get('IncomingOntologyClassEntity');
			}
		}
		
		return false;
	},
    getPropertyEntityByName : function(name) {
		for (var i=0; i < this.get('RelationOntologyClassOntologyPropertyEntities').models.length; i++) {
			if (this.get('RelationOntologyClassOntologyPropertyEntities').models[i].get('OntologyPropertyEntity').get('OntologyProperty').get('name') === name) {
				return this.get('RelationOntologyClassOntologyPropertyEntities').models[i].get('OntologyPropertyEntity');
			}
		}
		
		return false;
 	},
    getClassEntities : function(name) {
    	var classEntities = [];
		
		for (var i=0; i < this.get('RelationOntologyClassOntologyClassEntities').models.length; i++) {
			if (this.get('RelationOntologyClassOntologyClassEntities').models[i].get('IncomingOntologyClassEntity').get('OntologyClass').get('name') === name) {
				classEntities.push(this.get('RelationOntologyClassOntologyClassEntities').models[i].get('IncomingOntologyClassEntity'));
			}
		}
		
		
		for (var i=0; i < this.get('OntologyClass').get('RelationOntologyClassOntologyClasses').models.length; i++) {
			if (this.get('OntologyClass').get('RelationOntologyClassOntologyClasses').models[i].get('IncomingOntologyClass').get('name') === name) {
				var ontologyClass =  this.get('OntologyClass').get('RelationOntologyClassOntologyClasses').models[i].get('IncomingOntologyClass');
				
				
			}
		}
		
		if (classEntities.length == 0) {
			var classEntity = window["IncomingOntologyClassEntity"].findOrCreate({
				id : null,
				OntologyClass : ontologyClass
			});
			
			classEntities.push(classEntity);
		}
		
		
		var relsOCOP = ontologyClass.get('RelationOntologyClassOntologyProperties');
		
		for (var e=0; e < classEntities.length; e++) {
			for (var i=0; i < relsOCOP.models.length; i++) {
				if (!classEntities[e].hasPropertyByName(relsOCOP.models[i].get('OntologyProperty').get('name'))) {
					var relOCOPEntity = window["RelationOntologyClassOntologyPropertyEntity"].findOrCreate({
						id : null,
						OntologyPropertyEntity : new OntologyPropertyEntity({
							id : null,
							name: "",
							OntologyProperty : relsOCOP.models[i].get('OntologyProperty')
						})
					});
					
					classEntities[0].get('RelationOntologyClassOntologyPropertyEntities').push(relOCOPEntity);
				}
			}
		}
		
		return classEntities;
	},
	hasPropertyByName : function(name) {
		var relations = this.get('RelationOntologyClassOntologyPropertyEntities');
		
		for (var i = 0; i < relations.length; i++) {
    		if (relations.models[i].get('OntologyPropertyEntity').get('OntologyProperty').get('name') === name) {
    			return true;
    		}
    	}
		
		return false;
	}
	
});

window.OntologyClassEntityCollection = MasterCollection.extend({
	model : OntologyClassEntity,
	url : kmapiHost + "km/ontologyclassentities"
});


window.OutgoingOntologyClassEntity = OntologyClassEntity.extend({
	type : "OutoingOntologyClassEntity"
	
});
window.IncomingOntologyClassEntity = OntologyClassEntity.extend({
	type : "IncomingOntologyClassEntity"
	
});