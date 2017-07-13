var AccordionItemView = InputView.extend({
	tagName : "tr",
	initialize : function(options) {
		AccordionItemView.__super__.initialize.apply(this, arguments);
		
		this.template = _.template(tpl.get('components/accordionitem'));
		
		this.accordionfieldViews = [];

		this.baseModel = options.baseModel

		if (this.model.isConcrete()) {
			for(field in this.model.attributes) {
				//if (field !== "id" && field.indexOf("Outgoing") == -1 && field !== this.baseModel.type &&  (this.model.type.indexOf("Relation") !== -1 && this.model.type.indexOf(field.replace("Entity", "")) !== -1)) {
				if (field !== "id" && field.indexOf("Outgoing") == -1 && field !== this.baseModel.type) {
					fieldView = this.createFieldView(field, true);
					if (fieldView) {
						fieldView.withLabel = false;
						this.accordionfieldViews.push(fieldView);
					}
					
					var collectionModelAttributes = this.model.get(field).attributes;
					var keys = getKeysOfTypeObject(collectionModelAttributes);
					if (keys.length == 1) {
						for (var j = 0; j < keys.length; j++) {
							if (typeof this.model.get(field).get(keys[j]) === "object" && keys[j].indexOf("Relation") == -1) {
								fieldView = this.createFieldViewByModel(this.model.get(field), keys[j], true);
								if (fieldView) {
									fieldView.withLabel = false;
									this.accordionfieldViews.push(fieldView);
								}
							}
						}
					}
					
				}
			}
		} else {
			var relations_ococ = this.model.get('OntologyClass').get('RelationOntologyClassOntologyClasses');
			
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
				}
			}
		}
		
		
     	this.createDeleteButtonView();
		
	},
	events : {
		"click #btn_delete" : "deleteObject",
	},
	createDeleteButtonView : function() {
		this.deletebuttonView = new ButtonView({className: "btn btn-xs btn-primary pull-right", id: "btn_delete"});
	},
	deleteObject : function() {
		this.model.destroy();
		
		this.remove();
		
		return false;
	},
	render : function() {
		this.$el.empty();
		
		for (var i = 0; i < this.accordionfieldViews.length; i++) {
			this.$el.append($('<td style="padding: 2px; height: 34px;">').append(this.accordionfieldViews[i].render().el));
			this.accordionfieldViews[i].delegateEvents();
		}
		
		if (accessMode == "edit") {
			if(this.$('#panelitem_buttons').length == 0) {
				this.$el.append('<td id="panelitem_buttons" style="width: 80px; text-align: right;"></td>');
			}
			
			this.$("#panelitem_buttons").append(this.deletebuttonView.render().el);
			this.deletebuttonView.delegateEvents();
		} else {
			this.$el.append('<td id="panelitem_buttons" style="width: 80px; text-align: right;"></td>');
		}

		return this;
	}
});
		