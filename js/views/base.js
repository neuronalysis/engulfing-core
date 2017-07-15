var BaseView = Backbone.View.extend({
	initialize : function(options) {
		//this.viewFactory = new ViewFactory();
	},
	events : {
	},
	clear : function() {
		this.stopListening();
		this.undelegateEvents();
		this.$el.empty();

		return this;
	},
	createAdditionalFieldView : function(field) {
		for (var i=0; i < this.model.get('RelationOntologyClassOntologyPropertyEntities').models.length; i++) {
			var rel_entity = this.model.get('RelationOntologyClassOntologyPropertyEntities').models[i];
			if (rel_entity.get('OntologyPropertyEntity').get('OntologyProperty').get('name') === field) {
				var additionalfieldView = new InputLabelView({model: rel_entity.get('OntologyPropertyEntity')});
				additionalfieldView.field = field;

				return additionalfieldView;
			}
		}
	},
	focusView : function(item) {
		$("#context").html("");

		var input_id_element = $(item).closest('label');

		var newContextActionButton = new ButtonView({id: "btn_context_dataservice", model: this.model.boundOntologyClass});

		$("#context").append(newContextActionButton.render().el);
	},
	createEntityFieldView : function(field) {
		for (var i=0; i < this.model.get('RelationOntologyClassOntologyPropertyEntities').models.length; i++) {
			var rel_entity = this.model.get('RelationOntologyClassOntologyPropertyEntities').models[i];
			if (rel_entity.get('OntologyPropertyEntity').get('OntologyProperty').get('name') === field) {
				var fieldView = new InputTextView({model: rel_entity.get('OntologyPropertyEntity')});
				fieldView.field = field;

				return fieldView;
			}
		}
		for (var i=0; i < this.model.get('RelationOntologyClassOntologyClassEntities').models.length; i++) {
			var rel_entity = this.model.get('RelationOntologyClassOntologyClassEntities').models[i];
			if (rel_entity.get('IncomingOntologyClassEntity').get('OntologyClass').get('name') === field) {
				var fieldView = new InputSelectView({model: rel_entity});
				fieldView.field = 'IncomingOntologyClassEntity';

				fieldView.labelName = field;

				return fieldView;
			}
		}
	},
	createEntityGroupFieldView : function(field, classEntity) {
		for (var i=0; i < classEntity.get('RelationOntologyClassOntologyPropertyEntities').models.length; i++) {
			var rel_entity = classEntity.get('RelationOntologyClassOntologyPropertyEntities').models[i];
			if (rel_entity.get('OntologyPropertyEntity').get('OntologyProperty').get('name') === field) {
				var fieldView = new InputTextView({model: rel_entity.get('OntologyPropertyEntity')});
				fieldView.field = field;
				fieldView.url = '#' + rel_entity.get('OntologyPropertyEntity').get('name').replaceAll('/', '_');

				return fieldView;
			}
		}
		for (var i=0; i < classEntity.get('RelationOntologyClassOntologyClassEntities').models.length; i++) {
			var rel_entity = classEntity.get('RelationOntologyClassOntologyClassEntities').models[i];
			if (rel_entity.get('IncomingOntologyClassEntity').get('OntologyClass').get('name') === field) {
				var fieldView = new InputSelectView({model: rel_entity});
				fieldView.field = 'IncomingOntologyClassEntity';

				fieldView.labelName = field;

				return fieldView;
			}
		}
	},
	createFieldViewByModel : function(model, field, withCell, reference, compact) {
		var value = model.get(field);
		var value_type = typeof value;
		if (value_type === "object" && value) {
			if (value.models) {
				value.type = getSingular(field);
			} else {
				value.type = field;
			}
		} else {
			if (value === null) {
				if (field[0] === field[0].toUpperCase()) {
					value = new window[field];
					value.type = field;
					model.set(field, value);
				} else {
					value_type = "string";
				}
			}
		}

		var enumeration = null;
		var model_name;
		var model_name_set;
		var object;
		var object_working;


		if (value === null) {
			for (var i=0; i < model.relations.length; i++) {
				if (model.relations[i].key === field) {
					model_name =  model.relations[i].relatedModel;

					object = window[model_name];
					object_working = object.findOrCreate({id: null});

					if (object_working.__proto__.enumeration) {
						model_name_set = model_name;
						value_type = "enumeration";
						enumeration = object_working.__proto__.enumeration;
					} else {
						model_name_set = model_name;
						value = object_working;
						value.type = field;
					}
				}
			}
		} else {
			model_name =  "";

			object = null;
			object_working = null;

			for (var i=0; i < model.relations.length; i++) {
				if (model.relations[i].key === field) {
					model_name =  model.relations[i].relatedModel;

					object = window[model_name];
					object_working = object.findOrCreate({id: null});

					if (object_working.__proto__.enumeration) {
						model_name_set = model_name;
						value_type = "enumeration";
						enumeration = object_working.__proto__.enumeration;
					}
				}
			}
		}

		if (value_type === "string" || value_type === "number") {
			//TODO kick out domainspecific shit
			if (field !== "id" && field.slice(-2) !== "ID" && field !== "DataServices" && field !== "CourseDocument") {
				if (field.slice(-2) == "At" || field.slice(-4) == "Date") {
					var fieldView = new DatePickerView({model: model, field: field});

					return fieldView;
				} else if (field.slice(-10) == "Definition") {
					var fieldView = new InputTextAreaView({model: model, field: field});

					return fieldView;
				} else {
					var fieldView = new InputTextView({model: model, field: field});

					return fieldView;
				}
			}
		} else if (value_type === "boolean") {
			fieldView = new InputCheckBoxView({model: model, field: field});

			return fieldView;
		} else if (value_type === "enumeration") {
			var fieldView = new InputSelectView({model: model, field: field, enumeration: enumeration, withCell: withCell});

			return fieldView;
		} else if (value !== null) {
			if (typeof value === "object") {

				if (value.models) {
					if (field.indexOf("Relation") !== -1) {
						var fieldView = new AccordionGroupView({collection : model.get(field), field: field, baseModel : model});

						return fieldView;
					} else if (field.indexOf("Observations") !== -1) {
						var fieldView = new HighChartsView({model : model, field: field, observationsLimit: 250});

						return fieldView;
					} else {
						var fieldView = new InputTagsView({model: model, field: field});

						return fieldView;
					}

				} else {
					//TODO kick out domainspecific shit
					if (field == "CourseDocument") {
						var fieldView = new FileUploadView({model: model, field: field, withCell: withCell, referencingObject: reference});

						return fieldView;
					} else if (field == "ImpactFunction") {
						var fieldView = new ImpactFunctionView({model: model, field: field, withCell: withCell, withLabel: false});

						return fieldView;
					} else {
						var fieldView = new InputSelectView({model: model, field: field, withCell: withCell});
						//var fieldView = new InputSelectView({model: this.model, tagName: tagName});

						return fieldView;
					}
				}
			}
		}


		return false;
	},
	createFieldView : function(field, withCell) {
		return this.createFieldViewByModel(this.model, field, withCell, false);
	},
	createCompactFieldView : function(field, withCell) {
		return this.createFieldViewByModel(this.model, field, withCell, true);
	},
	assign : function (selector, view) {
	    var selectors;
	    if (_.isObject(selector)) {
	        selectors = selector;
	    }
	    else {
	        selectors = {};
	        selectors[selector] = view;
	    }
	    if (!selectors) return;
	    _.each(selectors, function (view, selector) {
	        view.setElement(this.$(selector)).render();
	    }, this);
	},
	renderTitle : function(title) {
		$(".page-header").attr("style", "display:show");

		if (title) {
			$("#title").html(title);
		} else {
			if (this.options) {
				$("#title").html(getPlural(this.options.objectName));
			} else if (this.model) {
				if (this.model.isConcrete()) {
					$("#title").html(this.model.type + ": " + this.model.get('name'));
				} else {
					$("#title").html(this.model.get('OntologyClass').get('name') + ": " + this.model.getNameProperty().get('name'));
				}

			} else {
				$("#title").html(getSiteMapTitle());
			}
		}
	}
});
