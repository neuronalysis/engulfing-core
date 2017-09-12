var SingleObjectView = BaseView.extend({
	tag : "form",
	id : "field-container",
	
	class : "form-horizontal",
	
	initialize : function() {
		this.template = _.template(tpl.get('layouts/singleobject'));

		this.fieldGroups = [];
		
		if (this.model.type !== "OntologyClass" && this.model.type !== "Ontology") this.fieldGroups = this.model.getFieldGroups();
		
		this.fieldViews = [];
		this.importProcesses = [];
		
		if (this.fieldGroups.length > 0) {
			this.hasFieldGroups = true;
			
			for (var i = 0; i < this.fieldGroups.length; i++) {
				if (this.fieldGroups[i].name === "Dashboard") {
					this.fieldGroups[i].fieldViews = this.getFieldViews();
				} else {
					this.fieldGroups[i].fieldViews = this.getGroupFieldViews(this.fieldGroups[i].name);
				}
			}
			
		} else {
			this.hasFieldGroups = false;
			
			this.fieldViews = this.getFieldViews();
		}
		
		this.contextButtonViews = [];
		
		if (accessMode == "read") {
			this.buttonView = new ButtonView({id: "btn_edit"});
		} else if (accessMode == "edit") {
			this.buttonView = new ButtonView({id: "btn_save"});
		}
		
		SingleObjectView.__super__.initialize.apply(this, arguments);
	},
	events : {
		"click #btn_save" : "saveObject",
		"click #btn_edit" : "editObject",
		"click #btn_addField" : "addField",
		"click #btn_endpoint" : "endpoint",
		"click #btn_entityList" : "entityList",
		"click #btn_endpoint" : "endpoint",
		"click #btn_watch" : "watchObject",
		"click #btn_addToBasket" : "addToBasket",
		"click #btn_ignore" : "ignoreObject",
		"click #btn_entityImport" : "entityImport",
		"click #btn_importProcessing" : "importProcessing"
	},
	addContextButton : function(newContextActionButton) {
		this.contextButtonViews.push(newContextActionButton);
		
	},
	addEntity : function(item) {
    	var url = window.location.href;

    	window.location = url + '/entities/new';
	},
	endpoint : function(item) {
    	var url = window.location.href;

    	window.location = url + '/endpoint';
 	},
 	//singleentity.js leftover
 	addField : function() {
		var ontologyProperty = window["OntologyProperty"].findOrCreate({
			id : null,
			name : null
		});
		var relOCOP = window["RelationOntologyClassOntologyProperty"].findOrCreate({
			id : null,
			OntologyClass : null,
			OntologyProperty : ontologyProperty
		});
		
		var ontologyPropertyEntity = window["OntologyPropertyEntity"].findOrCreate({
			id : null,
			name : null,
			OntologyProperty : ontologyProperty
		});
		var relOCOPEntity = window["RelationOntologyClassOntologyPropertyEntity"].findOrCreate({
			id : null,
			OntologyClassEntity : null,
			OntologyPropertyEntity : ontologyPropertyEntity
		});
		
		this.model.get('RelationOntologyClassOntologyPropertyEntities').models.push(relOCOPEntity);
		
		this.model.get('OntologyClass').get('RelationOntologyClassOntologyProperties').models.push(relOCOP);
		
		var entityFieldName = 'name';
		
		var newfieldView = this.createAdditionalFieldView(entityFieldName);
		
		this.fieldViews.push(newfieldView);
		
		this.render();
		
		return false;
	},
	watchObject : function() {
		if (app.activeView.model.id == this.model.id) {
			this.model.watch();
			
			this.model.isWatched = true;
			
			this.render();
		}
		
		return false;
	},
	addToBasket : function() {
		if (Cookie.get("Basket")) {
			if (app.activeView.model.id == this.model.id) {
				this.model.watch();
				
				this.model.isWatched = true;
				
				this.render();
			}
		} else {
			var basket = new Basket({});
			basket.addPosition(this.model);
			
			Cookie.set("Basket", JSON.stringify(basket));
		}
			
		
		
		return false;
	},
	importProcessing : function(item) {
		var targetID = parseInt(item.currentTarget.attributes.targetid.nodeValue);
		
		this.importProcesses[targetID].start(this.model.id);
		
		return false;
	},
	ignoreObject : function() {
		if (app.activeView.model.id == this.model.id) {
			this.model.ignore();
			
			this.model.isWatched = false;
			
			this.render();
		}
		
		return false;
	},
	editObject : function() {
		accessMode = "edit";
		
		this.render();
		
		return false;
	},
	entityImport : function() {
		var url = window.location.href;

    	window.location = url + '/entities/import';
	},
	entityList : function() {
		var url = window.location.href;

    	window.location = url + '/entities';
	},
	isGroupFieldView : function(fieldName) {
		if (this.fieldGroups === undefined) return false;
		
		for (var i = 0; i < this.fieldGroups.length; i++) {
			if (this.fieldGroups[i].name === fieldName) {
				return true;
			}
		}
		
		return false;
	},
	getFieldViews : function(model) {
		if (model === undefined) {
			model = this.model;
		}
		
		var fieldViews = [];
		
		if (!model) return fieldViews;
		
		//TODO for dashboard/overview fields from separate tabs/fieldgroups need to be prepared condensed or completely avoided
		if (model.isConcrete()) {
			if (model instanceof Backbone.Collection && model.length > 0) {
				if (model.type.substr(0, 8) === "Relation") {
					var subModelView = this.createFieldViewByModel(this.model, getPlural(model.type));
					fieldViews.push(subModelView);
				} else {
					if (relationName = model.hasRelation()) {
						for (var i=0; i < model.length; i++) {
							
							var subModelView = this.createFieldViewByModel(model.models[i], getPlural(relationName));
							fieldViews.push(subModelView);
						}
					} else {
						var subModelView = this.createFieldViewByModel(this.model, getPlural(model.type));
						fieldViews.push(subModelView);
					}
					
				}
			} else if (model instanceof Backbone.Collection && model.length == 0) {
				fieldView = this.createFieldViewByModel(this.model, getPlural(model.type));
				
				fieldViews.push(fieldView);
			} else {
				for(field in model.attributes) {
					if (field !== "id" && field.slice(-2) !== "ID" && !model.isProtected(field)) {
						if (!this.isGroupFieldView(field) && field !== this.model.type) {
							fieldView = this.createFieldViewByModel(model, field);
							
							fieldViews.push(fieldView);
						}
					} else if (field === "name") {
						if (accessMode === "edit") {
							fieldView = this.createFieldViewByModel(model, field);
							
							fieldViews.push(fieldView);
						}
					}
				}
			}
		} else {
			var relations_ococ = model.get('OntologyClass').get('RelationOntologyClassOntologyClasses');
			
			for (var i=0; i < relations_ococ.length; i++) {
				if (relations_ococ.models[i].get('OntologyRelationType').get('name') !== "extends" && relations_ococ.models[i].get('OntologyRelationType').get('name') !== "hasMany") {
					if (!relations_ococ.models[i].get('IncomingOntologyClass').isFieldGroup()) {
						var entityFieldName = relations_ococ.models[i].get('IncomingOntologyClass').get('name');
						
						var hasEntity = false;
						for (var j=0; j < this.model.get('RelationOntologyClassOntologyClassEntities').models.length; j++) {
							if (model.get('RelationOntologyClassOntologyClassEntities').models[j].get('IncomingOntologyClassEntity').get('OntologyClass').get('name') === entityFieldName) {
								hasEntity = true;
							}
						}
						
						if (!hasEntity) {
							var classEntity = window["IncomingOntologyClassEntity"].findOrCreate({
								id : null,
								OntologyClass : relations_ococ.models[i].get('IncomingOntologyClass')
							});
							
							var relClassEntity = window["RelationOntologyClassOntologyClassEntity"].findOrCreate({
								id : null,
								IncomingOntologyClassEntity : classEntity,
								OntologyRelationType : relations_ococ.models[i].get('OntologyRelationType')
							});
							
							this.model.get('RelationOntologyClassOntologyClassEntities').models.push(relClassEntity);
						}
						
						fieldView = this.createEntityFieldView(entityFieldName);
						
						fieldViews.push(fieldView);
					}
				//TODO special concrete information - model not available; root-cause needs to be resolved with clientside abstract2concrete converter
				/*} else if (relations_ococ.models[i].get('OntologyRelationType').get('name') === "hasMany") {
					var entityFieldName = relations_ococ.models[i].get('IncomingOntologyClass').get('name');
					
					if (entityFieldName === "InstrumentObservation") {
						var fieldView = new HighChartsView({model : null, field: "InstrumentObservations", observationsLimit: 250, modelID : this.model.id});
						
						fieldViews.push(fieldView);
					}*/
				}
					
			}
			
			var relations_ocop = this.model.get('OntologyClass').get('RelationOntologyClassOntologyProperties');
			
			for (var i=0; i < relations_ocop.length; i++) {
				var entityFieldName = relations_ocop.models[i].get('OntologyProperty').get('name');
				
				var hasEntity = false;
				for (var j=0; j < this.model.get('RelationOntologyClassOntologyPropertyEntities').models.length; j++) {
					if (this.model.get('RelationOntologyClassOntologyPropertyEntities').models[j].get('OntologyPropertyEntity').get('OntologyProperty').get('name') === entityFieldName) {
						hasEntity = true;
					}
				}
				if (!hasEntity) {
					var propertyEntity = window["OntologyPropertyEntity"].findOrCreate({
						id : null,
						OntologyProperty : relations_ocop.models[i].get('OntologyProperty')
					});
					
					var relPropertyEntity = window["RelationOntologyClassOntologyPropertyEntity"].findOrCreate({
						id : null,
						OntologyPropertyEntity : propertyEntity
					});
					
					this.model.get('RelationOntologyClassOntologyPropertyEntities').models.push(relPropertyEntity);
				}
				
				fieldView = this.createEntityFieldView(entityFieldName);
				
				fieldViews.push(fieldView);
			}
		}
		
		return fieldViews;
	},
	//TODO remove redundant code
	getGroupFieldViews : function (groupName) {
		var relations = this.model.getRelatedObjects();
		
		groupModel = this.model.getModelByGroupName(groupName);
		
		fieldViews = this.getFieldViews(groupModel);
		
		return fieldViews;
	},
	//TODO sync of model does not belong here
	//TODO approach for followup-navigation after save is shitty
	saveObject : function() {
		var input_type;
		
		var attrs = { }, k;
		for(k in this.model.attributes) {
	        attrs[k] = this.model.attributes[k];
	        if (k !== "id") {
	        	input_type = $('#' + k).prop('type');
	        	
	        	if (input_type === "text" || input_type === "password") {
	        		this.model.set(k, $('#' + k).val());
	        	} else {
	        		var objectValue = attrs[k];
	        		
	        		if (objectValue instanceof Backbone.Model) {
	        			if (objectValue.isEmpty()) {
	        				this.model.set(k, null);
	        			}
	        		}
	        	}
	        }
	    }
		
		if (!this.model.isNew()) {
			this.model.url = this.model.urlRoot + "/" + this.model.id;
		}
		
		this.model.save({}, {
		    success: function(model, response){
		    	if(typeof response.error === 'undefined'){
		    		//TODO risky change. not sure why url should be overwritten with collection's
		    		//model.url = model.urlRoot;
			    	
			    	if (model.type.substr(-6, 6) === "Entity") {
			    		var url = window.location.href;
			    		
			    		if (url.substr(-1) == '/') url = url.substr(0, url.length - 2);

				    	url = url.split('/');
				    	url.pop();
				    	
				    	var target = url[url.length-2] + "/entities/#" + model.id + "/";
				   	} else {
				   		var url = window.location.href;
			    		
				   		url = url.split('#');
				   		
				   		if (url[1].indexOf(model.type.toLowerCase()) !== -1) {
				   			url_1 = url[1];
				   			
				   			if (url_1.substr(-1) == '/') url_1 = url_1.substr(0, url_1.length - 2);
				   			
				   			url_1 = url_1.split('/');
				   			url_1.pop();
					    	
				   			app.navigate('#' + url_1[0] + "/" + model.id, true);
				   		} else {
				   			app.navigate('#' + model.id, true);
				   		}
				   		
			    	}
		    	} else {
		    		var alert_msg = '<div class="alert alert-danger">'+
		        	response.error.message+
					'<br/>' + response.error.details+
			    	'</div>';
	    			
	    			$('#alerts').html(alert_msg);
		    	}
		    	
		    },
	        error: function(model, response) {
	        	var alert_msg = '<div class="alert alert-danger">'+
	        	response.get('error').message+
				'<br/>' + response.get('error').details+
		    	'</div>';
    			
    			$('#alerts').html(alert_msg);
    			
	            //console.log(model);
	        }/*,
	        wait: true*/
		});
		
		accessMode = "read";
		
		this.render();
		
		return false;
	},
	render : function() {
		var data = {"hasFieldGroups": this.hasFieldGroups};

		this.$el.html(this.template(data));
		
		this.renderTitle();
 		
		if (this.hasFieldGroups) {
			this.renderFieldGroups();			
		} else {
			for (var i = 0; i < this.fieldViews.length; i++) {
				this.$("#field-container").append(this.fieldViews[i].render().el);
				this.fieldViews[i].delegateEvents();
			}
		}
		
		this.renderButtons();
				
		return this;
	},
	renderFieldGroups : function() {
		var fieldGroupTab = '';
		var tabContainerItem = '';
		var fieldGroupTabCaption = '';
		
		tabContainerItem = '<div role="tabpanel" class="tab-pane active" id="dashboard"></div>';
		
		this.$("#tab-container").html(tabContainerItem);
		
		fieldGroupTab = '<li role="presentation" class="active"><a href="#dashboard" id="dashboard-tab" role="tab" data-toggle="tab" aria-controls="dashboard" aria-expanded="true">Overview</a></li>';
		
		this.$("#object-tag-navigation").html(fieldGroupTab);
		for (var j = 0; j< this.fieldGroups[0].fieldViews.length; j++) {
			this.$("#dashboard").append(this.fieldGroups[0].fieldViews[j].render().el);
			this.fieldGroups[0].fieldViews[j].delegateEvents();
		}
		
		
		for (var i=1; i < this.fieldGroups.length; i++) {
			fieldGroupName = this.fieldGroups[i].name;
			if (fieldGroupName.substr(0, 8) === "Relation") {
				fieldGroupTabCaption = fieldGroupName.replace('Relation', '').replace(this.model.type, '');
			} else {
				fieldGroupTabCaption = fieldGroupName;
			}
			
			tabContainerItem = '<div role="tabpanel" class="tab-pane" id="' + fieldGroupTabCaption.toLowerCase() + '"></div>';
			this.$("#tab-container").append(tabContainerItem);
			
			fieldGroupTab = '<li role="presentation" class=""><a href="#' + fieldGroupTabCaption.toLowerCase() + '" id="' + fieldGroupTabCaption.toLowerCase() + '-tab" role="tab" data-toggle="tab" aria-controls="' + fieldGroupTabCaption.toLowerCase() + '" aria-expanded="true">' + fieldGroupTabCaption + '</a></li>';
			this.$("#object-tag-navigation").append(fieldGroupTab);
			
			for (var j = 0; j< this.fieldGroups[i].fieldViews.length; j++) {
				this.$('#' + fieldGroupTabCaption.toLowerCase() ).append(this.fieldGroups[i].fieldViews[j].render().el);
				this.fieldGroups[i].fieldViews[j].delegateEvents();
			}
		}
		
		
		

	},
	renderImportProcessingButton : function (name, importProcessID) {
		var importProcessingButton = new ButtonView({id: "btn_importProcessing", targetID: importProcessID});
		
		importProcessingButton.buttonObjectName = name;
		
		this.$("#sidebar").append('<br /><br /><br /><br />').append(importProcessingButton.render().el);
		importProcessingButton.delegateEvents();
	},
	//TODO renderButtons is ugly
	renderButtons : function() {
		if (Cookie.get("UserID")) {
			if (accessMode == "read") {
				this.buttonView.id = "btn_edit";
			} else if (accessMode == "edit") {
				this.buttonView.id = "btn_save";
			}
			
			this.$("#sidebar").append(this.buttonView.render().el);
			this.buttonView.delegateEvents();
			
			if (this.model.type === "OntologyClass") {
				var newEntityButton = new ButtonView({id: "btn_add_entity"});
				
				this.$("#sidebar").append(newEntityButton.render().el);
				newEntityButton.delegateEvents();
				
				var entitiesImportButton = new ButtonView({id: "btn_entityImport"});
				
				this.$("#sidebar").append('<br /><br /><br /><br />').append(entitiesImportButton.render().el);
				entitiesImportButton.delegateEvents();
				
				var entitiesButton = new ButtonView({id: "btn_entityList"});
				
				this.$("#sidebar").append('<br /><br />').append(entitiesButton.render().el);
				entitiesButton.delegateEvents();
			} else if (this.model.type === "Instrument" || this.model.type === "Indicator") {
				var entitiesImportButton = new ButtonView({id: "btn_entityImport"});
				
				this.$("#sidebar").append('<br /><br /><br /><br />').append(entitiesImportButton.render().el);
				entitiesImportButton.delegateEvents();
			}
			
			if (this.model.isWatched) {
				var ignoreObjectButton = new ButtonView({id: "btn_ignore"});
				
				self.$("#sidebar").append('<br /><br /><br /><br />').append(ignoreObjectButton.render().el);
				ignoreObjectButton.delegateEvents();
			} else {
				var watchObjectButton = new ButtonView({id: "btn_watch"});
				
				self.$("#sidebar").append('<br /><br /><br /><br />').append(watchObjectButton.render().el);
				watchObjectButton.delegateEvents();
			}
			
			if (this.model.type === "DataMartService") {
				var endpointButton = new ButtonView({id: "btn_endpoint"});
				
				this.$("#sidebar").append(endpointButton.render().el);
				endpointButton.delegateEvents();
			}
		} else {
			if (this.model.type == "User") {
				if (accessMode == "edit") {
					this.buttonView.id = "btn_save";
				}
				
				this.$("#sidebar").append(this.buttonView.render().el);
				this.buttonView.delegateEvents();
			}
		}	
		
		if (this.model.isInBasket) {
			var removeFromBasketButton = new ButtonView({id: "btn_removeFromBasket"});
			
			self.$("#sidebar").append('<br /><br /><br /><br />').append(removeFromBasketButton.render().el);
			removeFromBasketButton.delegateEvents();
		} else {
			var addToBasketButton = new ButtonView({id: "btn_addToBasket"});
			
			self.$("#sidebar").append('<br /><br /><br /><br />').append(addToBasketButton.render().el);
			addToBasketButton.delegateEvents();
		}
		
	}
});		