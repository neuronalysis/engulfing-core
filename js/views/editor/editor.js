var EditorView = BaseView.extend({
	initialize : function(options) {
		this.template = _.template(tpl.get('components/editor'));
		
		this.scanImage = options.scanImage;
		
		if (accessMode == "read") {
			this.buttonView = new ButtonView({id: "btn_edit"});
		} else if (accessMode == "edit") {
			this.buttonView = new ButtonView({id: "btn_save"});
		}
	},
	events : {
		"click #btn_save" : "saveObject",
		"click #btn_edit" : "editObject"
	},
	editObject : function() {
		accessMode = "edit";
		
		this.render();
		
		return false;
	},
	saveObject : function() {
		var input_type;
		
		var attrs = { }, k;
		
		this.model.save({}, {
		    success: function(model, response){
		    	if(typeof response.error === 'undefined'){
		    		model.url = model.urlRoot;
			    	
		    		app.navigate('#' + model.id, true);
		    	} else {
		    		var alert_msg = '<div class="alert alert-danger">'+
		        	response.error.message+
					'<br/>' + response.error.details+
			    	'</div>';
	    			
	    			$('#alerts').html(alert_msg);
		    	}
		    	
		    },
	        error: function(model, response) {
	        	var alert_msg = '<div class="alert alert-danger">'+
	        	response.get('error').message+
				'<br/>' + response.get('error').details+
		    	'</div>';
    			
    			$('#alerts').html(alert_msg);
	        },
		});
		
		accessMode = "read";
		
		this.render();
		
		return false;
	},
	changeValue : function(item) {
		if (typeof this.model.attributes.name === 'undefined') {
			this.model.set(item.target.id, item.target.value);
		} else {
			this.model.set('name', item.target.value);
		}
	},
	render : function() {
		var alto =  this.model.get('ALTO');
		var page = alto.get('Layout').get('Pages').models[0];
		
		
		var scanImage = this.scanImage;
		
		$(this.el).empty();
		$(this.el).append(new PageView({
			model : page
		}).render().el);
		
		/*$(this.el).append(new ScanImageView({
			model : scanImage
		}).render().el);
		*/
		this.renderButtons();
		
		return this;
	},
	renderButtons : function() {
		//if (Cookie.get("UserID")) {
			if (accessMode == "read") {
				this.buttonView.id = "btn_edit";
			} else if (accessMode == "edit") {
				this.buttonView.id = "btn_save";
			}
			
			//this.$("#sidebar").append(this.buttonView.render().el);
			$(this.el).append(this.buttonView.render().el);
			this.buttonView.delegateEvents();
			
		//}	
	}
});	