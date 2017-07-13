var InputView = BaseView.extend({
	initialize : function(options) {
		if(typeof options  !== 'undefined') {
			this.field = options.field;
			this.labelName = this.field;
			
			this.routeContext = options.routeContext;
			this.valueField = options.valueField;
			this.caption = options.caption;
			
			if(typeof options.withLabel  !== 'undefined') {
				this.withLabel = options.withLabel;
			} else {
				this.withLabel = true;
			}
			
			if (this.field === "name") {
				if (typeof this.model.type === 'undefined') {
					this.model.type = this.model.collection.type;
				}
				this.url = '../' + getPlural(this.model.type).toLowerCase() + '/#' + this.model.id;
			}
			
			
		} else {
			this.withLabel = true;
		}
		
		BaseView.__super__.initialize.apply(this, arguments);
	}
});
		