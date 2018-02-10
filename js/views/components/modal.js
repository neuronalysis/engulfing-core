var ModalView = BaseView.extend({
	initialize : function(options) {
		this.template = _.template(tpl.get('components/modal'));
		
		this.parent = options.parent;
		this.bodyText = options.bodyText;
		if (options.confirmationCallback) this.confirmationCallback = options.confirmationCallback.confirmationCallback;
		
	},
	events : {
		"click #btn_confirm" : "confirmQuestion",
	},
	confirmQuestion : function() {
		this.confirmationCallback(this.parent);
		
		this.$('#contextMenu').modal('toggle');
	},
	render : function() {
		var data = {"bodyText": this.bodyText};

		this.$el.html(this.template(data));
		
		return this;
	}
});
		