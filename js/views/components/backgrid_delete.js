var DeleteCell = Backgrid.Cell.extend({
	className: "integer-cell renderable",
	
	initialize : function() {
		this.template = _.template(tpl.get('components/backgrid_delete'));
	},
    events: {
      "click #btn_delete": "deleteRow"
    },
    deleteRow: function (e) {
      e.preventDefault();
      this.model.collection.remove(this.model);
      this.model.destroy();
    },
    render: function () {
      this.$el.html(this.template());
      this.delegateEvents();
      return this;
    }
});