window.TableEntitiesView = Backbone.View
		.extend({

			tagName : 'table',
			className : 'table table-striped table-bordered bootstrap-datatable datatable',
			initialize : function(options) {
				this.options = options;
				_.bindAll(this, 'render');
				
				this.template = _.template(tpl.get('table_entities'));
				this.model.bind("reset", this.render, this);
				var self = this;
				this.model.bind("add", function(object) {
					$(self.el).append(new TableEntitiesRowView({
						model : object
					}).render().el);
				});
			},

			render : function(eventName) {
				if (this.model.at(0) == null)
					return false;
				
				var data = {"data": this.model.at(0).toJSON(), "OntologyClass_id": this.options.OntologyClass_id};

				
				this.$el.html(this.template(data));

				_.each(this.model.models, function(object) {
					$(this.el).append(new TableEntitiesRowView({
						model : object, OntologyClass_id: this.options.OntologyClass_id
					}).render().el);
				}, this);

				return this;
			}

		});

window.TableEntitiesRowView = Backbone.View.extend({

	tagName : "tr",
	
	initialize : function(options) {
		this.options = options;
		_.bindAll(this, 'render');
		
		this.template = _.template(tpl.get('tableentitiesrow'));
		this.model.bind("change", this.render, this);
		//this.model.bind("destroy", this.close, this);
	},
	events : {
		'click #btn_remove' : 'deleteItem'
	},
	deleteItem : function(item) {
		this.model.destroy({
			success : function(model, response) {
				this.remove();
				//console.log("Success");
			},
			error : function(model, response) {
				var alert_msg = '<div class="alert alert-danger">'
						+ response.responseText + '</div>';

				$('#alerts').html(alert_msg);
			}
		});

		return false;
	},
	showEntities:function () {
    	location.href = 'http://Ontologies.localhost/OntologyClasses/#' + this.model.id + '/entities';
    	
	       	/*var ontologyClassentity = new OntologyClassEntity();
			
			var ontologyClassentity_view = new OntologyClassEntityView({el: $('#content'), model: OntologyClassentity});

			OntologyClassentity_view.render();*/

    },
    newEntity:function () {
    	location.href = 'http://Ontologies.localhost/OntologyClasses/#' + this.model.id + '/entities/new';
    	
	       	/*var ontologyClassentity = new OntologyClassEntity();
			
			var ontologyClassentity_view = new OntologyClassEntityView({el: $('#content'), model: OntologyClassentity});

			OntologyClassentity_view.render();*/

    },

	render : function(eventName) {
		var data = {"data": this.model.toJSON(), "OntologyClass_id": this.options.OntologyClass_id};

		$(this.el).html(this.template(data));
		return this;
	}

});