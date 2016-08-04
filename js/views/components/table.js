window.TableView = Backbone.View
		.extend({
			tagName : 'table',
			className : 'table table-striped table-bordered bootstrap-datatable datatable',
			initialize : function() {
				this.template = _.template(tpl.get('table'));
			},
			render : function(eventName) {
				var title = $('#title').html();
				
				var UserID = Cookie.get('UserID');
				var UserRoleID = Cookie.get('UserRoleID');
				
				if (this.collection.at(0) == null)
					return false;
				
				var data = {"data": this.collection.at(0).toJSON()};
				
				this.$el.html(this.template(data));
				
				
				_.each(this.collection, function(object) {
					$(this.el).append(new TableRowView({
						model : object
					}).render().el);
				}, this);
				
				$('#title').html(getPlural(title));
				
				return this;
			}

		});

window.TableRowView = Backbone.View.extend({

	tagName : "tr",
	
	initialize : function() {
		this.template = _.template(tpl.get('tablerow'));

	},
	events : {
		'click #btn_remove' : 'deleteItem',
		'click #btn_create' : 'newEntity',
		'click #btn_list' : 'showEntities'
	},
	deleteItem : function(item) {
		this.model.destroy({
			success : function(model, response) {
				//this.remove();
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
		location.href = 'http://localhost.ontologydriven/km/ontologyclasses/#' + this.model.id + '/entities';
    },
    newEntity:function () {
    	location.href = 'http://localhost.ontologydriven/km/ontologyclasses/#' + this.model.id + '/entities/new';
    },

	render : function() {
		var data = {"data": this.model.toJSON()};

		$(this.el).html(this.template(data));
		
		return this;
	}

});

window.TableCellView = Backbone.View.extend({

	tagName : "td",
	
	render : function() {
		$(this.el).html();
		
		return this;
	}

});