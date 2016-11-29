window.BackGridTableView = BaseView.extend({
	initialize : function(options) {
		if (options.tplPath) {
			this.template = _.template(tpl.get(options.tplPath));
		} else {
			this.template = _.template(tpl.get('components/backgrid'));
		}
		
		this.OntologyClass = options.OntologyClass;
		
		this.actions = [];
		
		this.columns = [ /*{
			name : "id",
			label : "ID",
			cell : UriCell.extend({
				target : '_self'
			}),
			href : function(rawValue, formattedValue, model) {
				var url = window.location.href;

		    	if (url.indexOf("entities") == -1) {
		    		return "#" + model.id;
		    	} else {
		    		if (url.substr(-1) == '/') url = url.substr(0, url.length - 2);

			    	url = url.split('/');
			    	url.pop();
			    	
			    	return url.join('/') + "/entities/#" + model.id;
		    	}
			},
			editable : false
		},*/
		    {
			name : "name",
			label : "Object Name",
			cell : UriCell.extend({
				target : '_self'
			}),
			href : function(rawValue, formattedValue, model) {
				var url = window.location.href;

		    	if (url.indexOf("entities") === -1) {
		    		return "#" + model.id;
		    	} else {
		    		if (model.collection.OntologyClass.get('isPersistedConcrete')) {
		    			if (url.substr(-1) == '/') url = url.substr(0, url.length - 2);

				    	url = url.split('/');
				    	//url.pop();
				    	
				    	return url[0] + '//' + url[1] + '/' + url[2] + '/wiki/articles/#' + model.get('name');
		    		} else {
		    			if (url.substr(-1) == '/') url = url.substr(0, url.length - 2);

				    	url = url.split('/');
				    	url.pop();
				    	
				    	return url.join('/') + "/entities/#" + model.id;
		    		}
		    		
		    	}
			},
			editable : false
		} ];
	},
	
	render : function() {
		this.$el.html(this.template());

		
		// Set up a grid to use the pageable collection
		var pageableGrid = new Backgrid.Grid({
			columns : this.columns,
			collection : this.collection
		});

		
		// Render the grid
		var $example2 = $("#backgrid-table");
		$example2.append(pageableGrid.render().el)

		// Initialize the paginator
		var paginator = new Backgrid.Extension.Paginator({
		  collection: this.collection
		});

		// Render the paginator
		$example2.after(paginator.render().el);

		// Initialize a client-side filter to filter on the client
		// mode pageable collection's cache.
		/*var filter = new Backgrid.Extension.ClientSideFilter({
		  collection: this.model,
		  fields: ['name']
		});*/

		// Render the filter
		//$example2.before(filter.render().el);

		// Add some space to the filter and move it to the right
		//$(filter.el).css({float: "right", margin: "20px"});

		// Fetch some data
		this.collection.fetch({reset: true, OntologyClass : this.OntologyClass});
		
		
		return this;
	}

});
