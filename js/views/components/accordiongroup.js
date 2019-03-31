var AccordionGroupView = InputView.extend({
	initialize : function(options) {
		AccordionGroupView.__super__.initialize.apply(this, arguments);
		
		this.collection.on('add', this.render, this);
		
		
		this.template = _.template(tpl.get('components/accordiongroup'));
		
		this.accordionItemViews = [];
		
		this.baseModel = options.baseModel;
		
		if (this.collection.length > 0) {
			for(var i=0; i<this.collection.length; i++) {
				this.collection.models[i].type = this.collection.type;
				
				accordionitemView = new AccordionItemView({model : this.collection.models[i], baseModel : this.baseModel});
				
				
				if (accordionitemView) {
					this.accordionItemViews.push(accordionitemView);
				}
			}
		}

     	this.createAddNewButtonView(this.field);
	},
	events : {
		"click #btn_add" : "addnewRelation",
	},
	//TODO does not belong here
	getOutgoingObjectField : function(relationModel) {
		for (field in relationModel.attributes) {
			if (field == "Outgoing" + this.baseModel.type || field == this.baseModel.type) {
				return field;
			}
		}
	},
	addnewRelation : function() {
		var relation_type = getSingular(this.field);
		
		var newRelation = window[relation_type].findOrCreate({
			id : null
		});
		
		//var outgoingObjectField = this.getOutgoingObjectField(newRelation);
		//newRelation.set(outgoingObjectField, this.baseModel);
		
		newRelation.type = relation_type;
		
		this.collection.add(newRelation);
		
		accordionitemView = new AccordionItemView({model : newRelation, baseModel : this.baseModel});
		
		if (accordionitemView) {
			this.accordionItemViews.push(accordionitemView);
		}
		
		this.render();
		
		return false;
	},
	createAddNewButtonView : function(field) {
		this.addnewbuttonView = new ButtonView({className: "btn btn-xs btn-primary pull-right", id: "btn_add"});
	},
	render:function () {
		this.reset;
		
		var data = {"object_name": this.collection.type.toLowerCase(), "field_name": this.collection.getFieldName(), "heading_title": this.baseModel.get('name')};
		
		this.$el.html(this.template(data));
		
		var collectionAttributes = this.collection.getModelAttributes();
		
		if (this.collection.models.length > 0) {
			for (var i = 0; i < collectionAttributes.length; i++) {
				
				if (collectionAttributes[i] !== "id" && collectionAttributes[i] !== this.baseModel.type && collectionAttributes[i].indexOf("Outgoing") == -1 && (this.collection.type.indexOf("Relation") !== -1 && this.collection.type.indexOf(collectionAttributes[i].replace("Entity", "")) !== -1)) {
					this.$("#relation_headers").append('<th>' + collectionAttributes[i] + "</th>");
					
					var collectionModelAttributes = this.collection.models[0].get(collectionAttributes[i]).attributes;
					var keys = getKeysOfTypeObject(collectionModelAttributes);
					if (keys.length == 1) {
						for (var j = 0; j < keys.length; j++) {
							if (typeof this.collection.models[0].get(collectionAttributes[i]).get(keys[j]) === "object" && keys[j].indexOf("Relation") == -1) {
								this.$("#relation_headers").append('<th>' + keys[j] + "</th>");
							}
						}
					}
				}
			}
			this.$("#relation_action").append('<div class="col-md-12" id="panelheader_buttons" style="text-align: right;"></div>');
			
			
			for (var i = 0; i < this.accordionItemViews.length; i++) {
				this.$("#relations").append(this.accordionItemViews[i].render().el);
				this.accordionItemViews[i].delegateEvents();
			}
	    	
			if (accessMode == "edit") {
				this.$("#panelheader_buttons").append(this.addnewbuttonView.render().el);
				this.addnewbuttonView.delegateEvents();
			}
		
		}
		return this;
    }
});
		