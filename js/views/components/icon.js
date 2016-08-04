var IconView = Backbone.View.extend({
	tagName : "button",
	className : "btn-danger btn-xs",
	
	getGlyphicon : function (action) {
		var glyphicon;
		if (action === "start") {
			glyphicon = "play";
		} else if (action === "generateOntology") {
			glyphicon = "cog";
		} else if (action === "generateWebsite") {
			glyphicon = "cog";
		} else if (action === "delete") {
			glyphicon = "remove";
		} else if (action === "entityList") {
			glyphicon = "list";
		} else if (action === "addEntity") {
			glyphicon = "plus";
		}
		
		return glyphicon;
	},
	render : function() {
		this.$el.attr('id', this.id);
		this.$el.html('<span class="glyphicon glyphicon-' + this.getGlyphicon(this.action) + '"></span>');
		
		this.$el.css({
		     'margin-left'          : "3px",
		     'height'				: "16px",
			 'width'				: "16px",
			 'font-size'			: "8px",
			 'padding-left'			: "3px"
		    });

		return this;
	}
});
		