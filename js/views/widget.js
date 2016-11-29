var WidgetView = SingleObjectView.extend({
	initialize : function() {
		SingleEntityView.__super__.initialize.apply(this, arguments);
	},
	events : {
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
		