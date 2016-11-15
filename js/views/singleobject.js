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
			
			for(field in this.model.__proto__.defaults) {
				if (field !== "id" && field.slice(-2) !== "ID" && field !== "DataServices") {
					fieldView = this.createFieldView(field);
					if (fieldView) {
						this.fieldViews.push(fieldView);
					}
				}
			}
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
		"click #btn_endpoint" : "endpoint",
		"click #btn_entityList" : "entityList",
		"click #btn_endpoint" : "endpoint",
		"click #btn_watch" : "watchObject",
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
	watchObject : function() {
		if (app.activeView.model.id == this.model.id) {
			this.model.watch();
			
			this.model.isWatched = true;
			
			this.render();
		}
		
		return false;
	},
	importProcessing : function(item) {
		var targetID = parseInt(item.currentTarget.attributes.targetid.nodeValue);
		
		this.importProcesses[targetID].start();
		
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
		for (var i = 0; i < this.fieldGroups.length; i++) {
			if (this.fieldGroups[i].name === fieldName) {
				return true;
			}
		}
		
		return false;
	},
	getFieldViews : function() {
		var fieldViews = [];
		
		for(field in this.model.attributes) {
			if (field !== "id" && field.slice(-2) !== "ID" && !this.model.isProtected(field)) {
				if (field.substring(0, 8) !== "Relation" && field.slice(-12) !== "Observations" && !this.isGroupFieldView(field)) {
					fieldView = this.createFieldView(field);
					
					fieldViews.push(fieldView);
				}
			}
		}
		
		return fieldViews;
	},
	getGroupFieldViews : function (groupName) {
		var fieldViews = [];
		
		var relations = this.model.getRelatedObjects();
		
		for (var i=0; i < relations.length; i++) {
			var entityFieldName = relations[i].related.type;
			
			if (entityFieldName === groupName) {
				
			} else if ("ImpactFunctions" === groupName && entityFieldName == "ImpactFunction") {
				var groupClassEntities = this.model.getEntities(entityFieldName);
				
				for (var e=0; e < groupClassEntities.length; e++) {
					
					var impactFunctionName = groupClassEntities[e].get('name');
					
					var indicatorsNames = "";
					
					fieldView = this.createFieldViewByModel(groupClassEntities[e], 'RelationIndicatorImpactFunctions', false);
					fieldViews.push(fieldView);
				}
			} else if ("Indicators" === groupName && entityFieldName == "RelationIndicatorImpactFunction") {
				var groupClassEntities = this.model.getEntities(entityFieldName);
				
				for (var e=0; e < groupClassEntities.length; e++) {
					for(field in groupClassEntities[e].attributes) {
						if (!this.model.isProtected(field) && (field === "name" || field === "date" || field === "Indicator")) {
							if (field.substring(0, 3) !== "Rel") {
								fieldView = this.createFieldViewByModel(groupClassEntities[e], field);
								
								fieldViews.push(fieldView);
							}
						}
					}
				}
			} else if ("ImpactFunctions" === groupName && entityFieldName == "RelationIndicatorImpactFunction") {
				var groupClassEntities = this.model.getEntities(entityFieldName);
				
				for (var e=0; e < groupClassEntities.length; e++) {
					for(field in groupClassEntities[e].attributes) {
						if (!this.model.isProtected(field) && (field === "name" || field === "date" || field === "ImpactFunction")) {
							if (field.substring(0, 3) !== "Rel") {
								fieldView = this.createFieldViewByModel(groupClassEntities[e], field);
								
								fieldViews.push(fieldView);
							}
						}
					}
				}
			} else if ("CourseDocuments" === groupName && entityFieldName == "CourseDocument") {
				var groupClassEntities = this.model.getEntities(entityFieldName);
				
				if (groupClassEntities.length == 0) {
					fieldView = this.createFieldViewByModel(new CourseDocument(), entityFieldName, false, this.model);
					
					fieldViews.push(fieldView);
				} else {
					for (var e=0; e < groupClassEntities.length; e++) {
						fieldView = this.createFieldViewByModel(groupClassEntities[e], entityFieldName, false, this.model);
						
						fieldViews.push(fieldView);
					}
				}
			} else if (getPlural(entityFieldName) === groupName) {
				if (groupName.indexOf("Observations") !== -1) {
					var chartsView = new HighChartsView({model : this.model, observationsLimit: 250});
					chartsView.field = groupName;
					
					fieldViews.push(chartsView);
				} else {
					var groupClassEntities = this.model.getEntities(entityFieldName);
					
					if (groupClassEntities.length == 1) {
						for (var e=0; e < groupClassEntities.length; e++) {
							for(field in groupClassEntities[e].attributes) {
								if (field !== "id" && field !== "name" && field.slice(-2) !== "ID" && !this.model.isProtected(field)) {
									if (field.substring(0, 3) !== "Rel") {
										fieldView = this.createFieldViewByModel(groupClassEntities[e], field);
										
										fieldViews.push(fieldView);
									}
								}
							}
						}
					} else {
						if (this.model.isConcrete()) {
							for (var e=0; e < groupClassEntities.length; e++) {
								for(field in groupClassEntities[e].attributes) {
									if (!this.model.isProtected(field) && (field === "name" || field === "date")) {
										if (field.substring(0, 3) !== "Rel") {
											fieldView = this.createFieldViewByModel(groupClassEntities[e], field);
											
											fieldViews.push(fieldView);
										}
									}
								}
							}
						} else {
							for (var e=0; e < groupClassEntities.length; e++) {
								
								var relOCOC_Properties = relations[i].get('IncomingOntologyClass').get('RelationOntologyClassOntologyProperties');
								for (var j=0; j < relOCOC_Properties.length; j++) {
									var groupClassPropertyName = relOCOC_Properties.models[j].get('OntologyProperty').get('name');
									
									if (relations[i].get('OntologyRelationType').get('name') === 'hasMany') {
										if (groupClassPropertyName === 'name') {
											fieldView = this.createEntityGroupFieldView(groupClassPropertyName, groupClassEntities[e]);
											
											fieldViews.push(fieldView);
										}
									} else {
										fieldView = this.createEntityGroupFieldView(groupClassPropertyName, groupClassEntities[e]);
										
										fieldViews.push(fieldView);
									}
								}
							}
						}
					}
				}
			}
		}
		
		return fieldViews;
	},
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
		    		model.url = model.urlRoot;
			    	
			    	if (model.type.substr(-6, 6) === "Entity") {
			    		var url = window.location.href;
			    		
			    		if (url.substr(-1) == '/') url = url.substr(0, url.length - 2);

				    	url = url.split('/');
				    	url.pop();
				    	
				    	var target = url[url.length-2] + "/entities/#" + model.id + "/";
				   	} else {
			    		app.navigate('#' + model.id, true);
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
		
		tabContainerItem = '<div role="tabpanel" class="tab-pane active" id="dashboard"></div>';
		
		this.$("#tab-container").html(tabContainerItem);
		
		for (var i=1; i < this.fieldGroups.length; i++) {
			tabContainerItem = '<div role="tabpanel" class="tab-pane" id="' + this.fieldGroups[i].name.toLowerCase() + '"></div>';
			this.$("#tab-container").append(tabContainerItem);
		}
		
		
		fieldGroupTab = '<li role="presentation" class="active"><a href="#dashboard" id="dashboard-tab" role="tab" data-toggle="tab" aria-controls="dashboard" aria-expanded="true">Overview</a></li>';
		
		this.$("#object-tag-navigation").html(fieldGroupTab);
		for (var j = 0; j< this.fieldGroups[0].fieldViews.length; j++) {
			this.$("#dashboard").append(this.fieldGroups[0].fieldViews[j].render().el);
			this.fieldGroups[0].fieldViews[j].delegateEvents();
		}
		
		for (var i=1; i < this.fieldGroups.length; i++) {
			fieldGroupTab = '<li role="presentation" class=""><a href="#' + this.fieldGroups[i].name.toLowerCase() + '" id="' + this.fieldGroups[i].name.toLowerCase() + '-tab" role="tab" data-toggle="tab" aria-controls="' + this.fieldGroups[i].name.toLowerCase() + '" aria-expanded="true">' + this.fieldGroups[i].name + '</a></li>';
			this.$("#object-tag-navigation").append(fieldGroupTab);
			
			for (var j = 0; j< this.fieldGroups[i].fieldViews.length; j++) {
				this.$('#' + this.fieldGroups[i].name.toLowerCase() ).append(this.fieldGroups[i].fieldViews[j].render().el);
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
	}
});		