tpl = {

	// Hash of preloaded templates for the app
	templates : {},

	// Recursively pre-load all the templates for the app.
	// This implementation should be changed in a production environment. All
	// the template files should be
	// concatenated in a single file.
	loadTemplates : function(templates, callback) {
		var that = this;

		var names = [ 'layouts/objectlist',
			'layouts/entitylist',
			'layouts/singleobject',
			'layouts/ontologyinformation',
			'layouts/concreteinformation',

			'components/accordiongroup',
			'components/accordionitem',
			'components/backgrid',
			'components/backgrid_actions',

			'components/editor',
				
			'components/input_datepicker',
			'components/input_textarea',
			'components/input_text',
			'components/input_image',
			'components/input_tags',
			'components/input_file',
			'components/input_highcharts',
			'components/input_pagination',
			'components/input_select',
			'components/input_locationmap',
			'components/input_datepicker',
			'components/input_checkbox'
				];

		names = names.concat(templates)
		
		var loadTemplate = function(index) {
			var name = names[index];
			var desc = '';
			var subDomain = '';
			
			if (templates.indexOf(name) > -1) {
				$.get(appHost + '/templates/' + name
						+ '.html', function(data) {
					that.templates[name] = data;
					index++;
					if (index < names.length) {
						loadTemplate(index);
					} else {
						callback();
					}
				});
			} else {
				$.get(engulfingBase + '/engulfing-core/templates/' + name
						+ '.html', function(data) {
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
		
		if (names.length > 0)
			loadTemplate(0);
	},
	
	// Get template by name from hash of preloaded templates
	get : function(name) {
		return this.templates[name];
	}

};
select2ConfigMin = {
	objectName : "",
	allowClear : true,
	placeholder : "Search",
	width : "180px",

	get : function(options, object_name) {
		this.objectName = object_name;

		this.placeholder = object_name.charAt(0).toUpperCase()
				+ object_name.slice(1);

		this.data = options;

		return this;
	},
	initSelection : function(item, callback) {
		var object = $(item).val();

		if (typeof object.models === 'undefined') {
			if (!object.hasOwnProperty('language')) {
				if (typeof object.name === 'undefined') {
					if (object.get('name') == '') {
						var nameById = '';

						for (var i = 0; i < object.enumeration.length; i++) {
							if (object.enumeration[i].id == object.id)
								nameById = object.enumeration[i].text;
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
				if (typeof object.get('language') === 'undefined') {
					var data = {
						id : object.id,
						text : object.get('name')
					};
				} else {
					var data = {
						id : object.id,
						text : object.get('name') + " ["
								+ object.get('language') + "]"
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
	allowClear : true,
	placeholder : "Search",
	width : "180px",
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

		if (typeof object.models === 'undefined') {
			if (!object.hasOwnProperty('language')) {
				if (typeof object.name === 'undefined') {
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
				if (typeof object.get('language') === 'undefined') {
					var data = {
						id : object.id,
						text : object.get('name')
					};
				} else {
					var data = {
						id : object.id,
						text : object.get('name') + " ["
								+ object.get('language') + "]"
					};
				}
			}

		} else {
			var data = [];

			for (var i = 0; i < object.models.length; i++) {
				if (typeof object.models[i].get('language') === 'undefined') {
					data.push({
						id : object.models[i].id,
						text : object.models[i].get('name')
					})
				} else {
					data.push({
						id : object.models[i].id,
						text : object.models[i].get('name') + " ["
								+ object.models[i].get('language') + "]"
					})
				}

			}

		}

		callback(data);
	},
	initConfig : function() {
		delete this.tags;
		this.multiple = false;
		delete this.tokenSeparators;

		return false;
	},
	get : function(url, object_name) {
		this.initConfig();

		this.objectName = object_name;
		this.ajax.url = url;

		this.placeholder = object_name.charAt(0).toUpperCase()
				+ object_name.slice(1);

		return this;
	},
	getTags : function(url, object_name, tags) {
		this.initConfig();

		this.objectName = object_name;
		this.ajax.url = url;

		this.placeholder = object_name.charAt(0).toUpperCase()
				+ object_name.slice(1);

		if (tags) {
			this.tags = tags;
			this.multiple = true;
			this.tokenSeparators = [ ",", " " ];

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
	allowClear : true,
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
	//TODO
	initSelection : function(item, callback) {
		var object = $(item).val();

		if (typeof object.models === 'undefined') {
			if (!object.hasOwnProperty('language')) {
				if (typeof object.name === 'undefined') {
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
				if (typeof object.get('language') === 'undefined') {
					var data = {
						id : object.id,
						text : object.get('name')
					};
				} else {
					var data = {
						id : object.id,
						text : object.get('name') + " ["
								+ object.get('language') + "]"
					};
				}
			}

		} else {
			var data = [];

			for (var i = 0; i < object.models.length; i++) {
				if (typeof object.models[i].get('language') === 'undefined') {
					data.push({
						id : object.models[i].id,
						text : object.models[i].get('name')
					})
				} else {
					data.push({
						id : object.models[i].id,
						text : object.models[i].get('name') + " ["
								+ object.models[i].get('language') + "]"
					})
				}

			}

		}

		callback(data);
	},
	initConfig : function() {
		delete this.tags;
		this.multiple = false;
		delete this.tokenSeparators;

		return false;
	},
	get : function(url, object_name) {
		this.initConfig();

		this.objectName = object_name;
		this.ajax.url = url;

		this.placeholder = object_name.charAt(0).toUpperCase()
				+ object_name.slice(1);

		return this;
	},
	getTags : function(url, object_name, tags) {
		this.initConfig();

		this.objectName = object_name;
		this.ajax.url = url;

		this.placeholder = object_name.charAt(0).toUpperCase()
				+ object_name.slice(1);

		if (tags) {
			this.tags = tags;
			this.multiple = true;
			this.tokenSeparators = [ ",", " " ];

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
//TODO
function getSubDomain() {
	var subdomain = "";
	
	//window.location.href.indexOf("localhost");
	subdomain = window.location.href;
	
	if (subdomain.indexOf("codegeneration") > -1) {
		var checkSplit = subdomain.split("codegeneration/");
		if (checkSplit[1].length > 0) {
			subdomain += "../";
		}
	} else if (subdomain.indexOf("admin") > -1) {
		var checkSplit = subdomain.split("admin/");
		if (checkSplit[1].length > 0) {
			subdomain += "../";
		}
	} else if (subdomain.indexOf("extraction") > -1) {
		var checkSplit = subdomain.split("extraction/");
		if (checkSplit[1].length > 0) {
			subdomain += "../";
		}
	} else if (subdomain.indexOf("usermanagement") > -1) {
		var checkSplit = subdomain.split("usermanagement/");
		if (checkSplit[1].length > 0) {
			subdomain += "../";
		}
	} else if (subdomain.indexOf("nlp") > -1) {
		var checkSplit = subdomain.split("nlp/");
		if (checkSplit[1].length > 0) {
			subdomain += "../";
		}
	} else if (subdomain.indexOf("km") > -1) {
		var checkSplit = subdomain.split("km/");
		if (checkSplit[1].length > 0) {
			subdomain += "../";
		}
	} else if (subdomain.indexOf("kokos") > -1) {
		var checkSplit = subdomain.split("kokos/");
		if (checkSplit[1].length > 0) {
			subdomain += "../";
		}
	}
	return subdomain;
}
function getBaseTemplatesRoot() {
	var root = "";

	return root;
}
function getKeysOfTypeObject(object, withoutRelations = true) {
	var keysOfType = [];
	var keys = Object.keys(object);
	
	for (var i = 0; i < keys.length; i++) {
		if (keys[i][0].toUpperCase() == keys[i][0]) {
			if (withoutRelations) {
				if (keys[i].indexOf("Relation") == -1) {
					keysOfType.push(keys[i]);
				}
			} else {
				keysOfType.push(keys[i]);
			}
			
		}
	}

	return keysOfType;
}
function loadMaps() {
	// Create Google map instance
	var places = new Backbone.GoogleMaps.LocationCollection([ {
		title : "Walker Art Center",
		lat : 44.9796635,
		lng : -93.2748776
	}, {
		title : "Science Museum of Minnesota",
		lat : 44.9429618,
		lng : -93.0981016
	} ]);

	var map = new google.maps.Map($('#TestLocation')[0], {
		center : new google.maps.LatLng(44.9796635, -93.2748776),
		zoom : 12,
		mapTypeId : google.maps.MapTypeId.ROADMAP
	});

	// Render Markers
	var markerCollectionView = new Backbone.GoogleMaps.MarkerCollectionView({
		collection : places,
		map : map
	});
	markerCollectionView.render();
}
/*function get_text_size(text, font) {
    this.element = document.createElement('canvas');
    this.context = this.element.getContext("2d");
    this.context.font = font;
    
    var tsize = {'width':this.context.measureText(text).width, 'height': parseInt(window.getComputedStyle(text).fontSize, 10)};
    
    return tsize;
}*/
function beyondBaseline(text) {
	var pattern = /[g|j|p|q|y]/;
	
	if (text) {
		if(text.match(pattern)) {
		    return true;
		}
	}
	
	
	return false;
}
function hasCapitalLetters(text) {
	if (text == text.toUpperCase()) {
		 return true;
	}
	
	return false;
}
function pageClickAll(pageNumber) {
    $("#page-number-all").text(pageNumber);
}
function get_text_width(txt, font) {
    this.element = document.createElement('canvas');
    this.context = this.element.getContext("2d");
    this.context.font = font;
    return this.context.measureText(txt).width;
}
function getSelectedText() {
	var text = "";
    if (window.getSelection) {
        text = window.getSelection().toString();
    } else if (document.selection && document.selection.type != "Control") {
        text = document.selection.createRange().text;
    }
    
    return text;
}
function getCursorPosition(ctrl) {
	var CaretPos = 0;
    // IE Support
   if (document.selection) {
       ctrl.focus ();
       var Sel = document.selection.createRange ();
       Sel.moveStart ('character', -ctrl.value.length);    
       CaretPos = Sel.text.length;
   }
   // Firefox support
   else if (ctrl.selectionStart || ctrl.selectionStart == '0')
       CaretPos = ctrl.selectionStart;
   return (CaretPos);
}
function getSelectedStart(text) {
	let selectedText = getSelectedText();
	
	return text.indexOf(selectedText);
}
function getSelectedEnd(text) {
	let selectedText = getSelectedText();
	
	return text.indexOf(selectedText) + selectedText.length;
}
function isSelectionRange() {
	selection = getSelectedText();
	
	if (selection.length > 1) return true;
	
	return false;
}