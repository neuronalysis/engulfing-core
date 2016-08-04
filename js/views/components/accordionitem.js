var AccordionItemView = BaseView.extend({
	tagName : "tr",
	initialize : function() {
		this.template = _.template(tpl.get('components/accordionitem'));
		
		this.accordionfieldViews = [];


		for(field in this.model.attributes) {
			if (field !== "id" && field.indexOf("Outgoing") == -1 && this.model.type.indexOf("Relation" + field.replace("Entity", "")) == -1) {
				fieldView = this.createFieldView(field, true);
				if (fieldView) {
					fieldView.withLabel = false;
					this.accordionfieldViews.push(fieldView);
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
		