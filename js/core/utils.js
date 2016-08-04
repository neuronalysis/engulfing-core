tpl = {

	// Hash of preloaded templates for the app
	templates : {},

	
	// Recursively pre-load all the templates for the app.
	// This implementation should be changed in a production environment. All
	// the template files should be
	// concatenated in a single file.
	loadTemplates : function(names, fromBase, callback) {

		var that = this;
		
		var loadTemplate = function(index) {
			
			var name = names[index];
			var desc = '';
			var subDomain = '';
			
			if (fromBase) {
				if (name.indexOf("../../") > -1) {
					name = name.replace("../../", "");
					
					$.get(engulfingBase + 'engulfing-core/templates/' + name + '.html', function(data) {
						that.templates[name] = data;
						index++;
						if (index < names.length) {
							loadTemplate(index);
						} else {
							callback();
						}
					});
				} else {
					name = name.replace("../../", "");
					
					if (window.location.href.replace(engulfingBase).match(new RegExp("/", "g")).length >= 1) {
						subDomain = window.location.href.replace(engulfingBase, "").split("/")[0];
						
					}
					$.get(engulfingBase + subDomain + '/templates/' + name + '.html', function(data) {
						that.templates[name] = data;
						index++;
						if (index < names.length) {
							loadTemplate(index);
						} else {
							callback();
						}
					});
				}
			
		
			} else {
				if (name.indexOf("../../") > -1) {
					desc = '../../';
					name = name.replace("../../", "");
				} else {
					if (name.indexOf("../") > -1) {
						desc = '../';
						name = name.replace("../", "");
					}
				}
				
				$.get(desc + 'templates/' + name + '.html', function(data) {
					that.templates[name] = data;
					index++;
					if (index < names.length) {
						loadTemplate(index);
					} else {
						callback();
					}
				});
			}
			
					
		};

		if (names.length > 0) loadTemplate(0);
	},

	/*loadTemplates : function(names, fromBase, callback) {
		var that = this;
		var desc = '';
		var name = '';
		
		console.log("base: " + engulfingBase);
		
		$.get(engulfingBase + 'templates/underscore.html', function(data) {
			var elements = $(data);
			
			for (var i = 0; i < names.length; i++) {
				if (names[i].indexOf("../../") > -1) {
					desc = '../../';
					name = names[i].replace("../../", "");
				} else {
					if (name.indexOf("../") > -1) {
						desc = '../';
						name = names[i].replace("../", "");
					}
				}
				
				var pickedElement = $.grep(elements, function(e){ 
					return e.id == name + '.html'; 
				});
				
				if (pickedElement.length === 1) {
					that.templates[name] =  $(pickedElement[0]).html();
				}
				
				if (i==names.length-1) {
					callback();
				}
			}
			
		});
		
		if (names.length <= 1) {
			this.loadTemplatesRecursively(names, fromBase, callback);
		}
		
	},*/
	// Get template by name from hash of preloaded templates
	get : function(name) {
		return this.templates[name];
	}

};
select2ConfigMin = {
	objectName : "",
	allowClear: true,
	placeholder : "Search",
	width : "180px",
	
	get : function(options, object_name) {
		this.objectName = object_name;
		
		this.placeholder = object_name.charAt(0).toUpperCase() + object_name.slice(1);
		
		this.data = options;
		
		return this;
	},
	initSelection : function(item, callback) {
		var object = $(item).val();
		
		if(typeof object.models  === 'undefined') {
			if(!object.hasOwnProperty('language')){
				if(typeof object.name  === 'undefined') {
					if (object.get('name') == '') {
						var nameById = '';
						
						for (var i=0; i < object.enumeration.length; i++) {
							if (object.enumeration[i].id == object.id) nameById = object.enumeration[i].text;
						}
						
						var data = {
								id : object.id,
								text : nameById
							};
					} else {
						var data = {
								id : object.id,
								text : object.get('name')
							};
					}
				} else {
					var data = {
							id : object.id,
							text : object.name
						};
				}
				
			} else {
				if(typeof object.get('language')  === 'undefined') {
					var data = {
							id : object.id,
							text : object.get('name')
						};
				} else {
					var data = {
							id : object.id,
							text : object.get('name') + " [" + object.get('language') + "]"
						};
				}
			}
			
			
		} else {
		}
			
		
		callback(data);
	}
};
select2Config = {
	objectName : "",
	allowClear: true,
	placeholder : "Search",
	width : "100%",
	minimumInputLength : 2,
	ajax : { // instead of writing the function to execute the request we use
				// Select2's convenient helper
		url : "",
		dataType : 'jsonp',
		quietMillis : 100,
		data : function(term, page) {
			return {
				query : term, // search term
			// page_limit: 10
			};
		},
		results : function(data, page) { // parse the results into the format
											// expected by Select2.
			// since we are using custom formatting functions we do not need to
			// alter remote JSON data
			return {
				results : data
			};
		}
	},

	createSearchChoice : function(term, data) {
		if ($(data).filter(function() {
			return this.text.localeCompare(term) === 0;
		}).length === 0) {
			return {
				id : -99,
				text : term
			};
		}
	},
	initSelection : function(item, callback) {
		var object = $(item).val();
		
		if(typeof object.models  === 'undefined') {
			if(!object.hasOwnProperty('language')){
				if(typeof object.name  === 'undefined') {
					var data = {
							id : object.id,
							text : object.get('name')
						};
				} else {
					var data = {
							id : object.id,
							text : object.name
						};
				}
				
			} else {
				if(typeof object.get('language')  === 'undefined') {
					var data = {
							id : object.id,
							text : object.get('name')
						};
				} else {
					var data = {
							id : object.id,
							text : object.get('name') + " [" + object.get('language') + "]"
						};
				}
			}
			
			
		} else {
			var data = [];
			
			for (var i = 0; i < object.models.length; i++) {
				if(typeof object.models[i].get('language')  === 'undefined') {
					data.push({
						id : object.models[i].id,
						text :object.models[i].get('name')
					})
				} else {
					data.push({
						id : object.models[i].id,
						text :object.models[i].get('name') + " [" + object.models[i].get('language') + "]"
					})
				}
				
			}
			
		}
			
		
		callback(data);
	},
	initConfig : function () {
		delete this.tags;
		this.multiple = false;
		delete this.tokenSeparators;
		
		return false;
	},
	get : function(url, object_name) {
		this.initConfig();
		
		this.objectName = object_name;
		this.ajax.url = url;

		this.placeholder = object_name.charAt(0).toUpperCase() + object_name.slice(1);
		
		return this;
	},
	getTags : function(url, object_name, tags) {
		this.initConfig();
		
		this.objectName = object_name;
		this.ajax.url = url;
		
		this.placeholder = object_name.charAt(0).toUpperCase() + object_name.slice(1);
		
		if (tags) {
			this.tags = tags;
			this.multiple = true;
			this.tokenSeparators = [",", " "];
			
		}
		
		
		return this;
	},

	// formatSelection: movieFormatSelection, // omitted for brevity, see the
	// source of this page
	dropdownCssClass : "bigdrop", // apply css that makes the dropdown taller
	escapeMarkup : function(m) {
		return m;
	} // we do not want to escape markup since we are displaying html in
		// results
};
select2QSConfig = {
		objectName : "",
		allowClear: true,
		placeholder : "Search",
		width : "250px",
		minimumInputLength : 3,
		ajax : { // instead of writing the function to execute the request we use
					// Select2's convenient helper
			url : "",
			dataType : 'jsonp',
			quietMillis : 100,
			data : function(term, page) {
				return {
					query : term, // search term
				// page_limit: 10
				};
			},
			results : function(data, page) { // parse the results into the format
												// expected by Select2.
				// since we are using custom formatting functions we do not need to
				// alter remote JSON data
				return {
					results : data
				};
			}
		},

		createSearchChoice : function(term, data) {
			if ($(data).filter(function() {
				return this.text.localeCompare(term) === 0;
			}).length === 0) {
				return {
					id : -99,
					text : term
				};
			}
		},
		initSelection : function(item, callback) {
			var object = $(item).val();
			
			if(typeof object.models  === 'undefined') {
				if(!object.hasOwnProperty('language')){
					if(typeof object.name  === 'undefined') {
						var data = {
								id : object.id,
								text : object.get('name')
							};
					} else {
						var data = {
								id : object.id,
								text : object.name
							};
					}
					
				} else {
					if(typeof object.get('language')  === 'undefined') {
						var data = {
								id : object.id,
								text : object.get('name')
							};
					} else {
						var data = {
								id : object.id,
								text : object.get('name') + " [" + object.get('language') + "]"
							};
					}
				}
				
				
			} else {
				var data = [];
				
				for (var i = 0; i < object.models.length; i++) {
					if(typeof object.models[i].get('language')  === 'undefined') {
						data.push({
							id : object.models[i].id,
							text :object.models[i].get('name')
						})
					} else {
						data.push({
							id : object.models[i].id,
							text :object.models[i].get('name') + " [" + object.models[i].get('language') + "]"
						})
					}
					
				}
				
			}
				
			
			callback(data);
		},
		initConfig : function () {
			delete this.tags;
			this.multiple = false;
			delete this.tokenSeparators;
			
			return false;
		},
		get : function(url, object_name) {
			this.initConfig();
			
			this.objectName = object_name;
			this.ajax.url = url;

			this.placeholder = object_name.charAt(0).toUpperCase() + object_name.slice(1);
			
			return this;
		},
		getTags : function(url, object_name, tags) {
			this.initConfig();
			
			this.objectName = object_name;
			this.ajax.url = url;
			
			this.placeholder = object_name.charAt(0).toUpperCase() + object_name.slice(1);
			
			if (tags) {
				this.tags = tags;
				this.multiple = true;
				this.tokenSeparators = [",", " "];
				
			}
			
			
			return this;
		},

		// formatSelection: movieFormatSelection, // omitted for brevity, see the
		// source of this page
		dropdownCssClass : "bigdrop", // apply css that makes the dropdown taller
		escapeMarkup : function(m) {
			return m;
		} // we do not want to escape markup since we are displaying html in
			// results
	};
function getBaseTemplatesRoot() {
	var root = "";
	
	return root;
}
function loadMaps() {
	// Create Google map instance
	var places = new Backbone.GoogleMaps.LocationCollection([
	    {
	        title: "Walker Art Center",
	        lat: 44.9796635,
	        lng: -93.2748776
	    },
	    {
	        title: "Science Museum of Minnesota",
	        lat: 44.9429618,
	        lng: -93.0981016
	    }
	]);

	var map = new google.maps.Map($('#TestLocation')[0], {
	    center: new google.maps.LatLng(44.9796635, -93.2748776),
	    zoom: 12,
	    mapTypeId: google.maps.MapTypeId.ROADMAP
	});

	// Render Markers
	var markerCollectionView = new Backbone.GoogleMaps.MarkerCollectionView({
	    collection: places,
	    map: map
	});
	markerCollectionView.render();
}