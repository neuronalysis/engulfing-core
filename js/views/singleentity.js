var SingleEntityView = SingleObjectView.extend({
	initialize : function() {
		SingleEntityView.__super__.initialize.apply(this, arguments);
	},
	events : {
		"click #btn_save" : "saveObject",
		"click #btn_addField" : "addField",
		"click #btn_watch" : "watchObject",
		"click #btn_ignore" : "ignoreObject",
		"click #btn_edit" : "editObject",
		"click #btn_importProcessing" : "importProcessing"
	},
	getFieldViews : function() {
		var fieldViews = [];
		
		for(field in this.model.attributes) {
			if (field !== "id" && field !== "name" && field.slice(-2) !== "ID" && !this.model.isProtected(field)) {
				if (field.substring(0, 3) !== "Rel") {
					fieldView = this.createFieldView(field);
					
					fieldViews.push(fieldView);
				}
			}
		}
		
		var relations_ococ = this.model.get('OntologyClass').get('RelationOntologyClassOntologyClasses');
		
		for (var i=0; i < relations_ococ.length; i++) {
			if (relations_ococ.models[i].get('OntologyRelationType').get('name') !== "extends" && relations_ococ.models[i].get('OntologyRelationType').get('name') !== "hasMany") {
				if (!relations_ococ.models[i].get('IncomingOntologyClass').isFieldGroup()) {
					var entityFieldName = relations_ococ.models[i].get('IncomingOntologyClass').get('name');
					
					
					
					var hasEntity = false;
					for (var j=0; j < this.model.get('RelationOntologyClassOntologyClassEntities').models.length; j++) {
						if (this.model.get('RelationOntologyClassOntologyClassEntities').models[j].get('IncomingOntologyClassEntity').get('OntologyClass').get('name') === entityFieldName) {
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
		
		return fieldViews;
	},
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
	saveObject : function() {
		var input_type;
		
		var relations_ocop = this.model.get('OntologyClass').get('RelationOntologyClassOntologyProperties');
		
		for (var i=0; i < relations_ocop.length; i++) {
			if (relations_ocop.models[i].isNew()) relations_ocop.models[i].save();
		}
		
			
		var attrs = { }, k;
		for(k in this.model.attributes) {
	        attrs[k] = this.model.attributes[k];
	        if (k !== "id") {
	        	input_type = $('#' + k).prop('type');
	        	
	        	//if (input_type !== "checkbox" && k.indexOf("relation") === -1) {
	        	if (input_type === "text") {
		        //if (input_type !== "checkbox" && k.indexOf("relation") === -1) {
	        		this.model.set(k, $('#' + k).val());
	        	}
	        }
	    }
		
		this.model.save({}, {
		    success: function(model){
		    	if (model.type.substr(-6, 6) === "Entity") {
		    		
		    		
		    		var url = window.location.href;
		    		
		    		if (url.substr(-1) == '/') url = url.substr(0, url.length - 2);

			    	url = url.split('/');
			    	url.pop();
			    	
			    	var target = url[url.length-2] + "/entities/#" + model.id + "/";
			    	
			    	
			    	//app.navigate("#212/entities/#46", true);
		    	} else {
		    		app.navigate('#' + model.id, true);
		    	}
		    }
		});
		
		accessMode = "read";
		
		this.render();
		
		return false;
	},
	render : function() {
		var data = {"hasFieldGroups": this.hasFieldGroups};

		this.$el.html(this.template(data));
		
		this.renderTitle(this.model.get('OntologyClass').get('name') + ": " + this.model.getNameProperty().get('name'));
 		
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
	}
});
		