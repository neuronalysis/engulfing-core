window.IntroView = BaseView.extend({

    initialize:function () {
		this.template = _.template(tpl.get('layouts/intro'));
    },

    events:{
    },
   
    render: function () {
		var data = {};
		
    	this.$el.html(this.template(data));
    	
    	return this;
    }
});
		