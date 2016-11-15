window.Master = Backbone.RelationalModel.extend({
	accessMode : "read",
	detailed : null,
	isWatched : null,
	
	fetch: function (options) {
		if (typeof options !== 'undefined') {
			if (options.detailed) {
	            this.url = this.urlRoot + "/" + this.id + "/detailed";
	        }
		}
		
		
		/*var self = this;
		
		$.when(watchlistPromise).then(function() {
			self.isWatched = user.get('Watchlist').hasItem(this);
		});*/
		
		
		/*
		if (user.get('Watchlist')) {
			for(item in user.get('Watchlist').get('WatchlistItems')) {
				if (this.type == "OntologyClass") {
					
				}
			}
		}*/
		

    	return  Backbone.RelationalModel.prototype.fetch.call(this, options);
    },
    getUrl: function () {
    	var url = '';
    	
    	url += odBase + 'wiki/articles#' + this.get('name');
    	
    	return url;
    },
    getRelatedObjects: function () {
    	if (this.isConcrete()) {
    		if (this.type !== "ReleasePublication") {
    			var relations = this.getRelations();
    		}
    	
    		for (var i=0; i < relations.length; i++) {
    			if (typeof relations[i].related.type === 'undefined') {
    				relations[i].related.type = relations[i].options.relatedModel;
    			}
    		}

    	} else {
    		var relations = this.get('OntologyClass').get('RelationOntologyClassOntologyClasses');

    		for (var i=0; i < relations.length; i++) {
    			if (relations.models[i].get('OntologyRelationType').get('name') !== "extends") {
    				
    				var related = new Object();
    				related.type = relations.models[i].get('IncomingOntologyClass').get('name');
    				relations.models[i].related = related;
    			}
    		}
    		
    		relations = relations.models;

    	}
    	
    	return relations;
    },
    getEntities: function (groupName) {
    	if (this.isConcrete()) {
    		if (typeof this.get(groupName) === 'undefined') {
    			var entities = this.get(getPlural(groupName)).models;
    		} else {
    			var entities = this.get(groupName).models;
    		}
    	} else {
    		var entities = this.getClassEntities(groupName);
    	}
    	
    	return entities;
    },
    getRelatedModel: function () {
    	return this.instance;
    },
    isEmpty: function () {
    	if (this.isNew()) {
    		if (!this.get('name') && this.type === "OntologyClassEntity") return true;
    		if (this.get('name') === '') return true;
    	}
    	
    	return false;
    },
    isProtected: function (fieldName) {
    	if (fieldName === 'Lexemes' || fieldName === 'Ressource' || fieldName === 'isPersistedConcrete') {
    		if (!isAuthorizedField(this.objectName, fieldName, Cookie.get("UserRoleID"))) {
    			return true;
    		}
    	}
    	
    	return false;
    },
    isFieldGroup: function () {
    	var relOCOC_Classes = this.get('RelationOntologyClassOntologyClasses');
    	if (relOCOC_Classes.length > 1) {
			return true;
    	}
    	
    	var relOCOC_Properties = this.get('RelationOntologyClassOntologyProperties');
		if (relOCOC_Properties.length > 1) {
			var nonIdentifiers = 0;
			
			for (var i=0; i < relOCOC_Properties.length; i++) {
				if (relOCOC_Properties.models[i].get('OntologyProperty').get('isIdentifier') == false) {
					nonIdentifiers++;
				}
			}
			
			if (nonIdentifiers > 1) {
				return true;
			}
		} else {
			if (relOCOC_Properties.length == 1) {
				if (relOCOC_Properties.models[0].get('OntologyProperty').get('name') !== "name") {
					return true;
				}
			}
			
		}
		
		return false;
    },
    isConcrete: function () {
    	if (this.type === "OntologyClassEntity" && this.get('OntologyClass')) {
    		return false;
    	}
    	
    	return true;
    },
    getFieldGroups: function () {
    	var fieldGroups = [];
    	
    	fieldGroups.push({"name" : "Dashboard", "fieldViews" : []} );
    	
    	if (this.isConcrete()) {
    		if (this.__proto__.relations.length > 0) {
    			for (var i=0; i < this.__proto__.relations.length; i++) {
    				if (this.__proto__.relations[i].key === getPlural(this.__proto__.relations[i].relatedModel) && this.__proto__.relations[i].key !== "Lexemes") {
    					var fieldGroupName = this.__proto__.relations[i].relatedModel;
        				
    					if (fieldGroupName.substr(0, 8) === "Relation") {
    						var relations_inout = this.get(this.__proto__.relations[i].key);
    			        	
    						var collectionModelAttributes = this.get('RelationIndicatorImpactFunctions').getModelAttributes();
    						
    						for (var j=0; j < collectionModelAttributes.length; j++) {
    							if (collectionModelAttributes[j] === this.type) {
    							} else {
    								var fieldGroupName = collectionModelAttributes[j];
    							}
    						}
    						
    					}
    				
        				fieldGroups.push({"name" : getPlural(fieldGroupName), "fieldViews" : []} );
    				}
    			}
    		} else {
    			
    		}
    	} else {
    		var relations_ococ = this.get('OntologyClass').get('RelationOntologyClassOntologyClasses');
        	
    		for (var i=0; i < relations_ococ.length; i++) {
    			if (relations_ococ.models[i].get('IncomingOntologyClass').isFieldGroup()) {
    				if (relations_ococ.models[i].get('OntologyRelationType').get('name') !== "extends" && relations_ococ.models[i].get('OntologyRelationType').get('name') !== "hasMany") {
        				var relOCOC_Properties = relations_ococ.models[i].get('IncomingOntologyClass').get('RelationOntologyClassOntologyProperties');
        				if (relOCOC_Properties.length > 1 || relOCOC_Properties.models[0].get('OntologyProperty').get('name') !== "name") {
        					var fieldGroupName = relations_ococ.models[i].get('IncomingOntologyClass').get('name');
        					
        					
        					fieldGroups.push({"name" : fieldGroupName, "fieldViews" : []} );
        				}
        			} else if (relations_ococ.models[i].get('OntologyRelationType').get('name') === "hasMany") {
        				var fieldGroupName = relations_ococ.models[i].get('IncomingOntologyClass').get('name');
        				
        				
        				fieldGroups.push({"name" : getPlural(fieldGroupName), "fieldViews" : []} );
        			}
				}
    		}
    	}
    	
    	
		return fieldGroups;
    },
	boundOntologyClass : null,

    bindOntologyClass : function(ontologyClass) {
    	var actions = [];
    	
    	var relsOCOC = ontologyClass.RelationOntologyClassOntologyClasses
    	for (var i = 0; i < relsOCOC.length; i++) {
    		if (relsOCOC[i].OntologyRelationType.name == "hasMany") {
    			if (relsOCOC[i].IncomingOntologyClass.name == "IndicatorObservation") {
    				this.boundOntologyClass = relsOCOC[i].IncomingOntologyClass;
    				//actions.push(this.model.prototype.relations[i].relatedModel);
    			} else if (relsOCOC[i].IncomingOntologyClass.name == "IndexObservation") {
    				this.boundOntologyClass = relsOCOC[i].IncomingOntologyClass;
    			}
    		}
    		
    	}
	},
	watch : function() {
		var watchlist = user.get('Watchlist');
		
		var watchlistitem = WatchlistItem.findOrCreate({
			Watchlist : Watchlist.findOrCreate({id: user.get('Watchlist').id}),
			OntologyClass : OntologyClass.findOrCreate({id: (this.type == "OntologyClass" ? 174 : this.get('OntologyClass').id)}),
			Entity : Entity.findOrCreate({id: (this.type == "OntologyClass" ? this.id : this.id)})
		});
		
		watchlistitemPromise = watchlistitem.save();

		$.when(watchlistitemPromise).then(function() {
			watchlist.get('WatchlistItems').add(watchlistitem);
			
			this.isWatched = true;
		});
	},
	ignore : function() {
		var watchlist = user.get('Watchlist');
		
		for(var i=0; i<watchlist.get('WatchlistItems').length; i++) {
			if (this.type == "OntologyClass") {
				if (watchlist.get('WatchlistItems').models[i].get('Entity')) {
					if ((parseInt(this.id) === parseInt(watchlist.get('WatchlistItems').models[i].get('Entity').id))) {
						watchlist.get('WatchlistItems').models[i].destroy({ dataType: "text", success: function(model, response) {
							watchlist.get('WatchlistItems').remove(model);
							
							this.isWatched = false;
				    	}});
					}
				} else {
					if (parseInt(this.id) === parseInt(watchlist.get('WatchlistItems').models[i].get('entityID'))) {
						watchlist.get('WatchlistItems').models[i].destroy({ dataType: "text", success: function(model, response) {
							watchlist.get('WatchlistItems').remove(model);
							
							this.isWatched = false;
				    	}});
					}
				}
			} else {
				if (watchlist.get('WatchlistItems').models[i].get('OntologyClass')) {
					if (parseInt(this.get('OntologyClass').id) === parseInt(watchlist.get('WatchlistItems').models[i].get('OntologyClass').id) && parseInt(this.id) === parseInt(watchlist.get('WatchlistItems').models[i].get('Entity').id)) {
						watchlist.get('WatchlistItems').models[i].destroy({ dataType: "text", success: function(model, response) {
							watchlist.get('WatchlistItems').remove(model);
							
							this.isWatched = false;
				    	}});
					}
				} else {
					if (parseInt(this.get('OntologyClass').id) === parseInt(watchlist.get('WatchlistItems').models[i].get('ontologyClassID')) && parseInt(this.id) === parseInt(watchlist.get('WatchlistItems').models[i].get('entityID'))) {
						watchlist.get('WatchlistItems').models[i].destroy({ dataType: "text", success: function(model, response) {
							watchlist.get('WatchlistItems').remove(model);
							
							this.isWatched = false;
				    	}});
					}
				}
			}
			
		}
	}
	    
	    
});

window.MasterCollection = Backbone.PageableCollection.extend({
	state : {
		pageSize : 15
	},
	fetch: function (options) {
		if (typeof options !== 'undefined') {
			if (options.OntologyClass) {
				this.OntologyClass = options.OntologyClass;
				
				this.url += "?ontologyClassID=" + this.OntologyClass.id;
			}
			if (options.internalKey) {
				this.url += "&internalKey=" + options.internalKey;
			}
			if (options.relatedOntologyClass) {
				this.url += "&relatedOntologyClassID=" + options.relatedOntologyClass.id;
			}
		}
			
    	return Backbone.PageableCollection.prototype.fetch.call(this, options)
    },
    parseState: function (resp, queryParams, state, options) {
      return {totalRecords: resp.total_count};
    },

    parseRecords: function (resp, options) {
      return resp.items;
    },
    getFieldName : function() {
		return getPlural(this.model.prototype.relations[1].relatedModel);
	},
    getModelAttributes : function() {
    	var attributes = [];
    	
    	for (var i = 0; i < this.model.prototype.relations.length; i++) {
    		attributes.push(this.model.prototype.relations[i].relatedModel);
    	}
    	
		return attributes;
	},
	getString : function() {
		var collectionString = "";
		
		for (var i = 0; i < this.models.length; i++) {
			if (i > 0) {
				collectionString += " " + this.models[i].get('name');
			} else {
				collectionString += this.models[i].get('name');
			}
		}
		
		return collectionString;
	}
});

window.Observation = Master.extend({
	
});
window.ObservationCollection = MasterCollection.extend({
	model : Observation,
	
	getLast : function() {
		return this.models[this.models.length - 1];
	}
});