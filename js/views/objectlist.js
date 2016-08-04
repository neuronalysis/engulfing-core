window.ObjectListView = BaseView.extend({
	initialize : function(options) {
		this.options = options;
		
		this.template = _.template(tpl.get('layouts/objectlist'));
		
		this.tableView		= new BackGridTableView({collection : this.collection});
		this.tableView.columns.push({
			name : "actions", // The key of the model attribute
			label : "Actions", // The name to display in the header
			sortable: false,
			editable : false,
			cell : ActionsCell.extend({
				orderSeparator : '',
				actions : ["delete"]
			})
		})
		this.buttonView 	= new ButtonView({id: "btn_add"});
	},
	events : {
		"click #btn_add" : "addnewObject"
	},
	addnewObject : function(item) {
		app.navigate('new', true);
		
		return false;
	},
	render : function() {
		this.$el.html(this.template());
		
		this.renderTitle();
 		
	    this.assign({
			'#table'       		: this.tableView
		});
		
	    this.$("#sidebar").append(this.buttonView.render().el);
	    this.buttonView.delegateEvents();
		if (!Cookie.get("UserID") && Cookie.get("logged") == 0) {
			
	    }
		
	    return this;
	}
});