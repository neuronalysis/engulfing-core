var EntityImportView = BaseView.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified

    initialize:function () {
        this.template = _.template(tpl.get('layouts/entityimport'));
        
        this.fieldViews = [];
		
		fieldView = this.createFileUploadFieldView("entitiesFile");
		if (fieldView) {
			this.fieldViews.push(fieldView);
		}
    },

    events:{
        "click #btn_parse":"parseResource"
    },
    createFileUploadFieldView : function(field) {
		var value_type = "file";
		
		if (value_type === "file") {
			fieldView = new FileUploadView();
			fieldView.field = field;
			fieldView.model = this.model;
			
			return fieldView;
		}
		
		return false;
	},
    render: function () {
		this.$el.html(this.template());
		
		this.renderTitle(this.model.get('name') + " Entities Import");
		
		this.$("#field-container").append(this.fieldViews[0].render().el);
		this.fieldViews[0].delegateEvents();
		
        return this;
    }
});