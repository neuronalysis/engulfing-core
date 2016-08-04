var GenerateCell = Backgrid.Cell.extend({
	className: "integer-cell renderable",
	
	initialize : function() {
		this.template = _.template(tpl.get('components/backgrid_generate'));
	},
    events: {
      "click #btn_generate": "generateOntology"
    },
    generateOntology: function (e) {
    	var url = window.location.href;

    	if (url.substr(-1) == '/') url = url.substr(0, url.length - 2);

    	url = url.split('/');
    	url.pop();

    	window.location = url.join('/') + '/codegenerator/#' + this.model.id;
    	
    	//app.navigate(url.join('/') + '/#' + this.model.id, {trigger: true, replace: true});
    	/*e.preventDefault();
      this.model.collection.remove(this.model);
      this.model.destroy();*/
    },
    render: function () {
    	var data = {"ontologyID": this.model.id};
		
    	this.$el.html(this.template(data));
    	this.delegateEvents();
    	return this;
    }
});