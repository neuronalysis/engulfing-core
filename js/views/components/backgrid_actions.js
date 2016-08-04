var ActionsCell = Backgrid.Cell.extend({
	className: "integer-cell renderable",
	
	initialize : function() {
		this.template = _.template(tpl.get('components/backgrid_actions'));

		this.iconViews = [];
		
		for (var i = 0; i < this.__proto__.actions.length; i++) {
			iconView = this.createIconView(this.__proto__.actions[i]);
			if (iconView) {
				this.iconViews.push(iconView);
			}
		}
	},
	events: {
	      "click #btn_delete": "deleteRow",
		  "click #btn_start": "startProcess",
		  "click #btn_generateOntology": "generateOntology",
		  "click #btn_generateWebsite": "generateWebsite",
		  "click #btn_entityList": "entityList",
		  "click #btn_addEntity": "addEntity"
	},
    deleteRow: function (e) {
        e.preventDefault();
        this.model.collection.remove(this.model);
        this.model.destroy();
    },
    startProcess: function (e) {
    	this.model.start();
    },
	addEntity : function(item) {
		var url = window.location.href;

    	/*if (url.substr(-1) == '/') url = url.substr(0, url.length - 2);

    	url = url.split('/');
    	url.pop();*/

    	window.location = url + '#' + this.model.id + '/entities/new';
	},
    entityList: function (e) {
    	var url = window.location.href;

    	/*if (url.substr(-1) == '/') url = url.substr(0, url.length - 2);

    	url = url.split('/');
    	url.pop();*/

    	//window.location = url.join('/') + '/ontologyclasses/#' + this.model.id + '/entities';
    	window.location = url + '#' + this.model.id + '/entities';
    },
    generateOntology: function (e) {
    	var url = window.location.href;

    	if (url.substr(-1) == '/') url = url.substr(0, url.length - 2);

    	url = url.split('/');
    	url.pop();

    	window.location = url.join('/') + '/ontologies/#' + this.model.id;
    },
    generateWebsite: function (e) {
    	var url = window.location.href;

    	if (url.substr(-1) == '/') url = url.substr(0, url.length - 2);

    	url = url.split('/');
    	url.pop();

    	window.location = url.join('/') + '/websites/#' + this.model.id;
    },
	createIconView : function(action) {
    	var iconView = new IconView({id: "btn_" + action});
		iconView.action = action;
		
		return iconView;
    },
    render: function () {
		this.$el.html(this.template());

		var data = {"ontologyID": this.model.id};
		
    	for (var i = 0; i < this.iconViews.length; i++) {
    		this.$("#actions").append(this.iconViews[i].render().el);
			this.iconViews[i].delegateEvents();
		}
    	
		return this;
    }
});