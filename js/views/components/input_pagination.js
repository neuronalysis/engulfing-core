var InputPaginationView = InputView.extend({
	initialize : function() {
		InputLabelView.__super__.initialize.apply(this, arguments);
		
		this.template = _.template(tpl.get('components/input_pagination'));
	},
	events : {
		"change" : "changeValue"
	},
	changeValue : function(item) {
		if (typeof this.model.attributes.name === 'undefined') {
			this.model.set(item.target.id, item.target.value);
		} else {
			this.model.set('name', item.target.value);
		}
	},
	render : function() {
		var $paginator = $("#backgrid-paginator");
		$example2.append(pageableGrid.render().el)

		// Initialize the paginator
		var paginator = new Backgrid.Extension.Paginator({
		  collection: this.collection
		});

		// Render the paginator
		$paginator.after(paginator.render().el);
        
		return this;
	}
});
		