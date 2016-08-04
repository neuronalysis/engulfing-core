window.EntityListView = BaseView.extend({
	initialize : function(options) {
		this.options = options;
		
		this.template = _.template(tpl.get('layouts/entitylist'));
		
		this.tableView		= new BackGridTableView({collection : this.collection, OntologyClass : this.options.OntologyClass});
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
		this.buttonView 	= new ButtonView({id: "btn_add_entity"});
	},
	events : {
		"click #btn_add_entity" : "addEntity"
	},
	addEntity : function(item) {
    	var url = window.location.href;

    	window.location = url + '/new';
	},
	render : function() {
		this.$el.html(this.template());
		
		$("#title").html(getPlural(this.options.objectName));
		
	    this.assign({
			'#table'       		: this.tableView
		});
		
	    this.$("#sidebar").append(this.buttonView.render().el);
	    this.buttonView.delegateEvents();
		
	    return this;
	}
});