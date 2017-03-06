var accessMode;
var user;
if (!Cookie.get("UserRoleID")) {
	$("#referer").val(window.location);
} else {
	var url = $("#signout").attr("href");
	$("#signout").attr("href", url + "?refererURL=" + window.location)
}
var BaseRouter = Backbone.Router.extend({
	objectName : null,

	activeView : null,
	
	initialize : function() {
		this.route("", "objectList");
		this.route(":id", "singleObject");
		this.route("new", "newObject");
		this.route(":id/entities", "entityList");
		this.route(":id/entities/new", "newEntity");
		this.route(":id/entities/#:entityID", "EntityDetails");
		this.route(":id/entities/import", "entityImport");
		this.route("housekeeping", "housekeeping");
	},
	isLoginFailure : function() {
		if (window.location.href.indexOf('login=failed') !== -1) {
			return true;
		}
		
		return false;
	},
	isAuthorized : function(routeType) {
		var roleID = Cookie.get("UserRoleID");
		
		$('#alerts').html('');
		/*var routeType;
		var caller = arguments.callee.caller;
		
		if (callerName === "objectList") {
			routeType = "list";
		}*/
		
		if (objects[this.objectName] !== undefined) {
			if (objects[this.objectName][roleID] !== undefined) {
				if(typeof(objects[this.objectName][roleID]) === "boolean"){
					if (objects[this.objectName][roleID]) {
						return true;
					}
				} else {
					if (objects[this.objectName][roleID][routeType]  !== undefined) {
						return true;
					}
				}
			} else if (objects[this.objectName][99] !== undefined) {
				return true;
			}
			
		} else {
			return true;
		}
		
		if (this.isLoginFailure()) {
			var alert_msg = '<div class="alert alert-danger">'+
			'<br/>' + 'Login failed.'+
			'<br/><br/>' + 'Please try again with your correct credentials.'+
			'<br/>' + 'If you don´t remember your credentials go to <a href="' + odBase + 'usermanagement/recovery">' + 'Password Recovery'+ '</a>.' +
			'<br/><br/>' + 'In case you´re not registered yet, you might want to <a href="' + odBase + 'usermanagement/register">' + 'Sign Up'+ '</a> here.' +
	    	'</div>';
		} else {
			var alert_msg = '<div class="alert alert-warning">'+
			'<br/>Access not granted.'+
			'<br/><br/>' + 'Please login to access this content.' +
			'<br/>' + 'If you don´t remember your credentials go to <a href="' + odBase + 'usermanagement/recovery">' + 'Password Recovery'+ '</a>.' +
			'<br/><br/>' + 'In case you´re not registered yet, you might want to <a href="' + odBase + 'usermanagement/register">' + 'Sign Up'+ '</a> here.' +
	    	'</div>';
		}
		
		
		$('#alerts').html(alert_msg);
		
		return false;
	},
	isWatchedObject : function (object, watchlist) {
		var watchlistItems = watchlist.get('WatchlistItems');
		
		var item;
		for(var i=0; i<watchlistItems.length; i++) {
			if (object.get('OntologyClass')) {
				if (parseInt(object.get('OntologyClass').id) === parseInt(watchlistItems.models[i].get('ontologyClassID')) && parseInt(object.id) === parseInt(watchlistItems.models[i].get('entityID'))) {
					return true;
				}
			} else if (object.type === "OntologyClass") {
				if (parseInt(object.id) === parseInt(watchlistItems.models[i].get('entityID'))) {
					return true;
				}
					
			}
			
		}
		
		return false;
	},
	objectList : function() {
		if (this.isAuthorized('list')) {
			var objectList = new window[this.objectName + "Collection"]();
			
			var objectlistView = new ObjectListView({el : $('#content'),
				collection : objectList, objectName : this.objectName});
		
			objectlistView.render();
		}
	},
	entityList : function() {
		if (this.isAuthorized('list')) {
			var entityList = new window[this.objectName + "Entity" + "Collection"]();
			
			var entitylistView = new EntityListView({el : $('#content'),
				collection : entityList, objectName : this.objectName});
		
			entitylistView.render();
		}
	},
	singleObject : function(id) {
		alert(id);
		if (this.isAuthorized('single')) {
			var object = window[this.objectName].findOrCreate({
				id : id
			});
			
			var dataservices = new DataServiceCollection();
			dataservicesPromise = dataservices.fetch();

			accessMode = "read";
			
			object.type = this.objectName;
			
			objectPromise = object.fetch();

			if (Cookie.get("UserID")) {
				user = User.findOrCreate({
					id : Cookie.get("UserID")
				});
				
				if (!user.get('Watchlist')) {
					var watchlist = new Watchlist();
					
					watchlist.urlRoot = user.urlRoot + "/" + user.id + "/watchlists";
					
					watchlistPromise = watchlist.fetch();
				} else {
					var watchlist = user.get('Watchlist');
				}
				
				var self = this;
				
				$.when(objectPromise, watchlistPromise).then(function() {
					user.set('Watchlist', watchlist);
					
					if (self.isWatchedObject(object, watchlist)) {
						object.isWatched = true;
					}
					var objectView = new SingleObjectView({
						el : $('#content'),
						model : object
					});
					
					objectView.render();
					
					app.activeView = objectView;
				});
			} else {
				$.when(objectPromise).then(function() {
					var objectView = new SingleObjectView({
						el : $('#content'),
						model : object
					});
					
					objectView.render();
					
					app.activeView = objectView;
					
					var ontologyClass = new OntologyClass();
					
					ontologyClass.urlRoot = ontologyClass.urlRoot + "?name=" + object.type;
					
					ontologyClassPromise = ontologyClass.fetch();
					
					$.when(ontologyClassPromise).then(function() {
						var ontologyInformationView = new OntologyInformationView({
							el : $('#ontologyInformation'),
							model : ontologyClass
						});
						
						$("#ontologyInformation").append(ontologyInformationView.render().el);
					});
					
					
				});
			}
		}
	},
	EntityDetails : function(id, entityID) {
		var entityBase = window[this.objectName].findOrCreate({
			id : id
		});
		entityBase.type = this.objectName;
		
		
		entityBasePromise = entityBase.fetch();

		$.when(entityBasePromise).then(function() {
			var object = window[entityBase.type + "Entity"].findOrCreate({
				id : entityID
			});
			
			object.type = entityBase.type + "Entity";
			
			object.set(entityBase.type, entityBase);
			
			accessMode = "read";
			
			object.fetch({
				success : function() {
					var objectView = new SingleEntityView({
						el : $('#content'),
						model : object
					});
					
					objectView.render();
					
					var watchlist = new Watchlist();
					
					watchlist.urlRoot = self.user.urlRoot + "/" + self.user.id + "/watchlists";
					
					objectView.user.set('Watchlist', watchlist);
					
					watchlist.fetch();
				}
			});
		});
		
	},
	entityImport : function(id) {
		if (isAuthorized(this.objectName, Cookie.get("UserRoleID"))) {
			var object = window[this.objectName].findOrCreate({
				id : id
			});
			
			accessMode = "read";
			
			object.type = this.objectName;
			
			objectPromise = object.fetch();

			$.when(objectPromise).then(function() {
				var entityImportView = new EntityImportView({
					el : $('#content'),
					model : object
				});
				
				entityImportView.render();
			});
		} else {
			var alert_msg = '<div class="alert alert-warning">'+
			'Authentication Warning'+
			'<br/>' + 'Login Failed'+
			'<br/>' + '<a href="http://localhost.ontologydriven/usermanagement/recovery">' + 'Password Recovery '+ '</a>' +
	    	'</div>';
			
			$('#alerts').html(alert_msg);
		}
	},
	newObject : function() {
		var object = window[this.objectName].findOrCreate({
			id : null
		});
		
		accessMode = "edit";
		
		object.type = this.objectName;

		var objectView = new SingleObjectView({
			el : $('#content'),
			model : object
		});
		
		objectView.render();
	},
	newEntity : function(id) {
		var entityBase = window[this.objectName].findOrCreate({
			id : id
		});
		entityBase.type = this.objectName;
		
		
		entityBasePromise = entityBase.fetch();

		$.when(entityBasePromise).then(function() {
			var object = window[entityBase.type + "Entity"].findOrCreate({
				id : null
			});
			object.type = entityBase.type + "Entity";
			
			object.set(entityBase.type, entityBase);
			
			accessMode = "edit";
			
			var objectView = new SingleEntityView({
				el : $('#content'),
				model : object
			});
			
			objectView.render();

		});
	},

	intro : function() {
		intro_view = new IntroView({
			el : $('#content')
		});

		intro_view.render();
	}
});