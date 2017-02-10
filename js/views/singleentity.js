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
	        	
	        	if (input_type === "text") {
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
		