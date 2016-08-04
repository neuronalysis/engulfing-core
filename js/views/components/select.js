window.SelectView = Backbone.View
		.extend({
			tagName : "select",

			initialize : function(data) {
				this.model = data.model;
				this.options = data.options;
				this.fieldName = data.fieldName;
			},

			events : {
				'change' : 'onselect'
			},
			onselect : function() {
				if (this.el.options[this.el.selectedIndex]) {
					var attribute = {};
					attribute[this.fieldName] = this.el.options[this.el.selectedIndex].value;
					
					this.model.set(attribute);
				}

				return this;
			},

			render : function() {
				for (var i = 0; i < this.options.length; i++) {
					if (this.options[i].id == this.model.get(this.fieldName)) {
						this.$el.append('<option value="'
								+ this.options[i].id
								+ '" selected="true">'
								+ this.options[i].value
								+ '</option>');
					} else {
						this.$el.append('<option value="'
								+ this.options[i].id
								+ '">'
								+ this.options[i].value
								+ '</option>');
					}
				}

				this.onselect();

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