var LocationMapView = InputView.extend({
	initialize : function(model, field) {
		this.model = model;
		this.field = field;
		
		this.template = _.template(tpl.get('components/input_locationmap'));
		
		/*this.map = new google.maps.Map(
            this.el,
            this.model.toJSON()
        );*/
        //this.render();
        
		
		
	},
	events : {
		
	},
	changeValue : function(item) {
	},
	render : function() {
		this.$el.html(this.template());

	    var map = L.map(this.$('#map')[0]).setView([55.75, 37.58], 10);
	    L.tileLayer('http://{s}.tile.cloudmade.com/4e5f745e28654b7eb26aab577eed79ee/997/256/{z}/{x}/{y}.png', {
	      attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>[…]',
	      maxZoom: 18
	    }).addTo(map);

	    return this;
	}
});
		