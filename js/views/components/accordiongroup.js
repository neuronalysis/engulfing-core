var AccordionGroupView = BaseView.extend({
	initialize : function() {
		this.collection.on('add', this.render, this);
		
		this.template = _.template(tpl.get('components/accordiongroup'));
		
		this.accordionItemViews = [];
		
		if (this.collection.length > 0) {
			for(var i=0; i<this.collection.length; i++) {
				this.collection.models[i].type = this.collection.type;
				
				accordionitemView = new AccordionItemView({model : this.collection.models[i]});
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
	addnewRelation : function() {
		var relation_type = getSingular(this.field);
		
		var newRelation = window[relation_type].findOrCreate({
			id : null
		});
		
		newRelation.type = relation_type;
		
		this.collection.add(newRelation);
		
		accordionitemView = new AccordionItemView({model : newRelation});
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
		
		var data = {"object_name": this.collection.type.toLowerCase(), "field_name": this.collection.getFieldName()};
		
		this.$el.html(this.template(data));
		
		var collectionAttributes = this.collection.getModelAttributes();
		
		for (var i = 0; i < collectionAttributes.length; i++) {
			if (collectionAttributes[i] !== "id" && collectionAttributes[i].indexOf("Outgoing") == -1 && (this.collection.type.indexOf("Relation") !== -1 && this.collection.type.indexOf(collectionAttributes[i].replace("Entity", "")) !== -1)) {
				this.$("#relation_headers").append('<th>' + collectionAttributes[i] + "</th>");
			}
			
			if (collectionAttributes[i] == "ImpactFunction") {
				var impactfunction = this.collection.models[0].get('ImpactFunction');
				
				impactfunction.url = apiHost + "economics/impactfunctions/" + this.collection.models[0].id + "/valuation";
				
				impactfunction.fetch({
					success : function(impactfunction) {
						var result = impactfunction;
						
						var test = "asdf";
						
					}
				});
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
		
		return this;
    }
});
		