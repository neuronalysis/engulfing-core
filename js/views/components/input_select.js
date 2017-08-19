//TODO 	get rid of ontology related stuff; try to convert abstract concepts into 
//		concrete ones before passing them to frontend-components
//TODO merge InputSelectView and SelectView
var InputSelectView = InputView.extend({
	
	initialize : function(options) {
		InputSelectView.__super__.initialize.apply(this, arguments);
		
		this.template = _.template(tpl.get('components/input_select'));
		
		/*this.labelName = this.field;
		
		this.withLabel = true;
		
		if (options.withCell) {
			this.withCell = options.withCell;
		} else {
			this.withCell = false;
		}*/
		
	},
	events : {
		"change" : "changeValue",
		"click" : "focusView"
	},
	changeValue : function(item) {
		var model_name = item.target.id;
		
		if (item.added) {
			if (item.added.name) {
				model_object = window[model_name].findOrCreate({
					id: item.added.id,
					name: item.added.name
				});
			} else {
				model_object = window[model_name].findOrCreate({
					id: item.added.id,
					name: item.added.text
				});
			}
			
			if (model_name === "IncomingOntologyClassEntity") {
				var propertyEntity = window["OntologyPropertyEntity"].findOrCreate({
					id : null,
					name : item.added.text,
					OntologyProperty : this.model.get('IncomingOntologyClassEntity').get('OntologyClass').getOntologyPropertyByName('name')
				});
				
				var relPropertyEntity = window["RelationOntologyClassOntologyPropertyEntity"].findOrCreate({
					id : null,
					OntologyPropertyEntity : propertyEntity
				});
				
				model_object.get('RelationOntologyClassOntologyPropertyEntities').models.push(relPropertyEntity);
				
				model_object.set('OntologyClass', this.model.get('IncomingOntologyClassEntity').get('OntologyClass'));
			}
			
			this.model.set(model_name, model_object);
		} else if (item.removed) {
			model_object = window[model_name].findOrCreate({
				id: item.removed.id
			});
			
			model_object.clear();
		}
		
		return model_object;
	},
	render : function() {
		if (!this.labelName) {
			this.labelName = this.field;
		}
		
		var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "field_value": this.model.get(this.field), "withCell" : this.withCell, "withLabel" : this.withLabel, "labelName" : this.labelName};
		
		var model_select = this.model.get(this.field);
		if (!model_select) {
			model_select = new window[this.field];
			model_select.type = this.field;
		}
		if (model_select.type === "IncomingOntologyClassEntity") {
			if (model_select.get('OntologyClass') && (model_select.urlRoot.indexOf("ontologyClassID") === -1)) {
				model_select.urlRoot += "?ontologyClassID=" + model_select.get('OntologyClass').id;
			}
		}
		if (accessMode == "edit") {
			this.$el.html(this.template(data));
			
			var object = this.model.get(this.field);
			
			if (this.options) {
				this.$("#" + model_select.type).select2(select2ConfigMin.get(this.options, model_select.type));
				this.$("#" + model_select.type).select2('val', object);
			} else {
				this.$("#" + model_select.type).select2(select2Config.get(model_select.urlRoot, this.labelName));
				if (this.model) {
					if (model_select.type === "IncomingOntologyClassEntity") {
						var object = model_select.getNameProperty();
						
						if (object) {
							this.$("#" + model_select.type).select2('val', object);
						}
					} else {
						this.$("#" + model_select.type).select2('val', model_select);
					}
				}
			}
		} else if (accessMode == "read") {
			if (this.model) {
				var object_string = "";
				
				if (model_select.type === "OntologyClassEntity" || model_select.type === "IncomingOntologyClassEntity") {
					var object = model_select.getNameProperty();
					
					if (object) {
						if (model_select.id) {
							object_string += '<a href="#' + object.get('name') + '">' + object.get('name') + '</a>';
						} else {
							object_string += object.get('name');
						}
						
						data.field_id = object.get('id');
						data.field_value = object_string;
					} else {
						data.field_id = null;
						data.field_value = "";
					}
					
					
				} else {
					var object = this.model.get(this.field);
					
					if (object) {
						if (object.attributes) {
							if (object.get('name')) {
								if (this.field === "IncomingOntologyClass") {
									object_string += '<a href="../../km/ontologyclasses/#' + object.get('id') + '">' + object.get('name') + '</a>';
								} else {
									ontologyName = getOntology(getSingular(this.field.toString().toLowerCase()));
									if (ontologyName !== "") {
										object_string += '<a href="../../' + ontologyName + '/' + getPlural(this.field).toLowerCase() + '/#' + object.get('id') + '">' + object.get('name') + '</a>';
									} else {
										object_string += '<a href="../../' + getPlural(this.field).toLowerCase() + '/#' + object.get('id') + '">' + object.get('name') + '</a>';
									}
								}
							} else {
								if (object.get('text')) {
									object_string += object.get('text');
								}
							}
							
							data.field_id = object.get('id');
							data.field_value = object_string;
						} else {
							if (object.name) object_string += object.name;
							
							data.field_id = object.id;
							data.field_value = object_string;
						}
					} else {
						data.field_id = null;
						data.field_value = null;
					}
				}
			} else {
				var enumeration = null;
				
				for (var i=0; i < this.model.relations.length; i++) {
					if (this.model.relations[i].key === this.field) {
						model_name =  this.model.relations[i].relatedModel;
						
						object = window[model_name];
						object_working = object.findOrCreate({id: null});
						
						if (object_working.__proto__.enumeration) {
							model_name_set = model_name;
							value_type = "enumeration";
							enumeration = object_working.__proto__.enumeration;
						} else {
							model_name_set = model_name;
							value = object_working;
						}
					}
				}
				
				if (enumeration) {
					var object_string = "";
					object_string += enumeration[this.model.get(scope + "ID")].text;
					
					data.field_value = object_string;
				}
			}
			
			this.$el.html(this.template(data));
		}
		
		
		return this;
	}
});
		