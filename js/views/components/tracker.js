window.TrackerView = Backbone.View.extend({
	//tagName : "ul",
	//className : "media-list",

	initialize : function(options) {
		this.collection = options.collection;
	},
	
	renderChanges : function(scope) {
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
				if (scope == 'page') {
					changesHTML += '<li class="list-group-item">' +
					'<i>' + object.get('Page').updatedByUser.name + '</i>' + 
				  	' changed <i>' + object.get('before') + '</i> to <i>' + object.get('after') + '</i>' +
				  	' at <i>' + object.get('Page').updatedAt + '</i>' +
				  '</li>';
				} else {
					changesHTML += '<li class="list-group-item">' +
					'<i>' + object.get('Page').updatedByUser.name + '</i>' + 
				  	' changed <i>' + object.get('before') + '</i> to <i>' + object.get('after') + '</i>' +
				  	' on <a href="./editor/#documents/' + object.get('Page').documentID + '/pages/' +  + object.get('Page').number + '">page <i>' +object.get('Page').number + ' of document</i></a>' +
				  	' at <i>' + object.get('Page').updatedAt + '</i>' +
				  '</li>';
				}
				
			}, this);
			
		
			
			changesHTML += '</ul>';
			
			self.$el.append(changesHTML);
		});
		
		return this;
	},
	renderRegistrations : function() {
		var titleHTML = '';
		titleHTML += '<h2>Registrations</h2>';
		
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
					'<i>' + object.get('name') + '</i>' + 
				  	' registered at <i>' + object.get('createdAt') + '</i>' +
				  '</li>';
			}, this);
			
		
			
			changesHTML += '</ul>';
			
			self.$el.append(changesHTML);
		});
		
		return this;
	},
	renderRankings : function() {
		var titleHTML = '';
		titleHTML += '<h2>Rankings</h2>';
		
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
					'<i>' + object.get('position') + '.</i>' + 
				  	' <i>' + object.get('user').name + '</i>' +
				  	' with <i>' + object.get('changes') + ' ' + (object.get('changes') > 1 ? 'changes' : 'change') +
				  '</li>';
			}, this);
			
			changesHTML += '</ul>';
			
			self.$el.append(changesHTML);
		});
		
		return this;
	}
});