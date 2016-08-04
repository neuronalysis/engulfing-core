var StartCell = Backgrid.Cell.extend({
	className: "integer-cell renderable",
	
	initialize : function() {
		this.template = _.template(tpl.get('components/backgrid_start'));
	},
    events: {
      "click #btn_start": "startProcess"
    },
    startProcess: function (e) {
    	this.model.start();
    },
    render: function () {
    	var data = {"ontologyID": this.model.id};
		
    	this.$el.html(this.template(data));
    	this.delegateEvents();
    	return this;
    }
});