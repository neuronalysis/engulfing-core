var InputTagsView = InputView.extend({
	initialize : function() {
		InputTagsView.__super__.initialize.apply(this, arguments);
		
		if(typeof this.caption  === 'undefined') {
			this.caption = 'name';
    	}
				
		this.template = _.template(tpl.get('components/input_tags'));
	},
	events : {
		"change" : "changeValue"
	},
    changeValue:function (item) {
    	var object = window[this.model.get(this.field).type];
		
       	if(typeof item.removed  !== 'undefined') {
    		var object_removed = object.findOrCreate({
				id: item.removed.id
			});
    		
    		this.model.get(this.field).remove(object_removed);
    		
    		object_removed.set(this.model.type.toLowerCase() + "ID", null);
    		object_removed.save();
    	} else if(typeof item.added  !== 'undefined') {
    		/*var object_added = object.findOrCreate({
				id: item.added.id
			});*/
    		
    		this.model.get(this.field).push(item.added);
    	}
		
        return this.model;
    },
	render : function() {
		var model_tags = this.model.get(this.field);
		
		var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "field_value": model_tags.getString(), "withCell" : this.withCell, "withLabel" : this.withLabel};
		
		
		if (accessMode == "edit") {
			this.$el.html(this.template(data));
			
			this.$("#" + this.field).select2(select2Config.getTags(model_tags.__proto__.url, model_tags.type.toLowerCase(), true, model_tags.models));
	    	
	    	var tags = [];
			
	    	
			for (var i = 0; i < model_tags.models.length; i++) {
				if (typeof model_tags.models[i].attributes.language !== 'undefined') {
					tags.push({
						id : model_tags.models[i].id,
						text : model_tags.models[i].get(this.caption) + " [" + model_tags.models[i].get('Language').get('isoCode') + "]"
					});
				} else {
					tags.push({
						id : model_tags.models[i].id,
						text : model_tags.models[i].get(this.caption)
					});
				}
			}
			
			this.$("#" + this.field).select2('data', tags);
		} else if (accessMode == "read") {
			var tags = [];
			
			var object_string = "";
			//TODO 	needs support for sub-page
			//		currently workaround with explicit specific overwrite
			if(typeof this.routeContext  !== 'undefined') {
				pathRoute = getOntology(getSingular(this.field.toLowerCase())) + '/' + this.routeContext + '/' + getPlural(this.field.toLowerCase()) + '/';
			} else {
				pathRoute = getOntology(getSingular(this.field.toLowerCase())) + '/' + getPlural(this.field.toLowerCase()) + '/#';
			}
			
			for (var i = 0; i < model_tags.models.length; i++) {
				if(typeof this.valueField  !== 'undefined') {
					entityRoute = model_tags.models[i].get(this.valueField);
				} else {
					entityRoute = model_tags.models[i].get('id');
				}
				
				object_string += '<a href="../../' + pathRoute + entityRoute + '">' + model_tags.models[i].get(this.caption) + '</a>';
				
				if (i < model_tags.models.length - 1) {
					object_string += ', ';
				}
			}
			
			
			
			data.field_value = object_string;
			
			
			this.$el.html(this.template(data));
		}
		
		
		
		return this;
	}
});
		