//TODO merge InputSelectView and SelectView
window.SelectView = Backbone.View.extend({
	tagName : "select",
	className :  "form-control",
	
	initialize : function(data) {
		this.id = data.id;
		this.model = data.model;
		this.options = data.options;
		this.fieldName = data.fieldName;
		this.caption = data.caption;
		this.onSelectRoute = data.onSelectRoute;
		if (typeof data.valueField !== 'undefined') {
			this.valueField = data.valueField;
		} else {
			this.valueField = 'id';
		}
		
	},

	events : {
		'change' : 'onSelect'
	},
	//TODO get rid of app specifics
	onSelect : function() {
		if (typeof this.onSelectRoute !== 'undefined') {
			if (this.fieldName === 'number') {
				var subRoute = '#documents/' + this.options.models[0].get('documentID') + '/pages/' + this.el.options[this.el.selectedIndex].value;
				var gotoUrl = this.onSelectRoute + subRoute;
				
				Backbone.history.navigate(subRoute, {trigger: true})
			} else if (this.fieldName === 'version') {
				var subRoute = '#documents/' + this.model.get('documentID') + '/pages/' + this.model.get('number') + '/version/' + this.el.options[this.el.selectedIndex].value;
				var gotoUrl = this.onSelectRoute + subRoute;
				
				Backbone.history.navigate(subRoute, {trigger: true})
			}
			
		} else {
			if (this.el.options[this.el.selectedIndex]) {
				var attribute = {};
				attribute[this.fieldName] = this.el.options[this.el.selectedIndex].value;
				
				this.model.set(attribute);
			}

			return this;
		}
			
		
		
	},

	render : function() {
		if (this.options instanceof Backbone.Collection) {
			for (var i = 0; i < this.options.length; i++) {
				if(typeof this.model !== 'undefined') {
					if (this.options.models[i].get(this.valueField) == this.model.get(this.fieldName)) {
						this.$el.append('<option value="'
								+ this.options.models[i].get(this.valueField)
								+ '" selected="true">'
								+ this.caption + ' ' + this.options.models[i].value
								+ '</option>');
					} else {
						this.$el.append('<option value="'
								+ this.options.models[i].get(this.valueField)
								+ '">'
								+ this.caption + ' ' + this.options.models[i].value
								+ '</option>');
					}
				} else {
					this.$el.append('<option value="'
							+ this.options.models[i].get(this.valueField)
							+ '">'
							+ this.caption + ' ' + this.options.models[i].value
							+ '</option>');
				}
			}
		} else if (this.options instanceof Array) {
			//TODO 	get rid of version mgmt related stuff
			//		dependencies:	all non-backbone-collection related select-options (e.g. kokos)
			if (this.fieldName === 'version') {
				for (var i = 0; i < this.options.length; i++) {
					if(typeof this.model !== 'undefined') {
						if (this.options[i].version === this.model.get(this.fieldName)) {
							this.$el.append('<option value="'
									+ this.options[i].version
									+ '" selected="true">'
									+ this.caption + ' ' + this.options[i].version
									+ '</option>');
						} else {
							this.$el.append('<option value="'
									+ this.options[i].version
									+ '">'
									+ this.caption + ' ' + this.options[i].version
									+ '</option>');
						}
					} else {
						this.$el.append('<option value="'
								+ this.options[i].version
								+ '">'
								+ this.caption + ' ' + this.options[i].version
								+ '</option>');
					}
				}
			} else if (this.fieldName === 'zoomFactor') {
				for (var i = 0; i < this.options.length; i++) {
					if(typeof this.model !== 'undefined') {
						if (this.options[i].zoomFactor === editorOptions['zoomFactor']) {
							this.$el.append('<option value="'
									+ this.options[i].zoomFactor
									+ '" selected="true">'
									+ this.caption + ' ' + this.options[i].zoomFactor
									+ '</option>');
						} else {
							this.$el.append('<option value="'
									+ this.options[i].zoomFactor
									+ '">'
									+ this.caption + ' ' + this.options[i].zoomFactor
									+ '</option>');
						}
					} else {
						this.$el.append('<option value="'
								+ this.options[i].version
								+ '">'
								+ this.caption + ' ' + this.options[i].version
								+ '</option>');
					}
				}
			}
		}
		

		//this.onselect();

		return this;
	}
});

var OptionView = Backbone.View.extend({

	tagName : "option",
	render : function() {
		var name = this.model.get("name");
		var id = this.model.get("id");

		if (id == this.options.OntologyrelationtypeID) {
			this.$el.html(name).val(id);
			this.$el.prop('selected', true);
		} else {
			this.$el.html(name).val(id);
		}

		return this;
	}
});