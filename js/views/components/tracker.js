window.TrackerView = Backbone.View.extend({
	//tagName : "ul",
	//className : "media-list",

	initialize : function(options) {
		this.collection = options.collection;
	},
	
	render : function() {
		var titleHTML = '';
		titleHTML += '<h2>Changes</h2>';
		
		this.$el.append(titleHTML);
		
		var differenceCollection = this.collection;
		
		differenceCollectionPromise = differenceCollection.fetch({reset: true});
		
		var self = this;
		
		var spinnerHTML = '';
		spinnerHTML += '<div class="track-changes-spinner-div">' +
	      '<span class="glyphicon glyphicon-refresh spin"></span>';
		spinnerHTML += '</div>';
		
		self.$el.append(spinnerHTML);
		
		$.when(differenceCollectionPromise).then(function() {
			self.$("div.track-changes-spinner-div").remove();

			var changesHTML = '';
			changesHTML += '<ul class="list-group">';
			
			_.each(differenceCollection.models, function(object) {
				changesHTML += '<li class="list-group-item">' +
				  '<span class="badge">' + object.get('key') + '</span>' +
				  object.get('before') + ' vs. ' + object.get('after') +
				  '</li>';
			}, this);
			
		
			
			changesHTML += '</ul>';
			
			self.$el.append(changesHTML);
		});
		
		return this;
		
		
		
		
		//this.$el.append(titleHTML).append(subtitleHTML).append(chartsView.render().$el);
		//$.when(differenceCollectionPromise).then(function() {
			//_.each(differenceCollection, function(object) {
			//	self.$("div.track-changes-spinner-div").append('<div>arsch</div>');
			//}, this);
			
			//self.$("div.track-changes-spinner-div").replaceWith(
			//		);
			/*self.$("div.track-changes-spinner-div").replaceWith(
					'<h4 class="media-heading">' + '<a href="' + teaserModel.getUrl() + '">' + teaserModel.get('name') + '</a>' + ' last updated on ' + new Date(teaserModel.get('lastIndicatorObservationDate')).format() + '</h4>'
					);
			*/
			
		//});
		

		return this;
	}
});