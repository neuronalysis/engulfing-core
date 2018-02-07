window.TrackerView = Backbone.View.extend({
	//tagName : "ul",
	//className : "media-list",

	initialize : function(options) {
		this.collection = options.collection;
	},
	events : {
		"mouseover .list-group-item" : "hooverArea"
	},
	//TODO doublicate implemenation in string.js - consolidate
	hooverArea : function(item) {
		if (typeof editorOptions !== 'undefined') {
			if (editorOptions['imageAvailable']) {
				$(".list-group-item").hover(function() {
				    $(this).css('cursor','pointer');
				}, function() {
				    $(this).css('cursor','auto');
				});
				
				let xCoor = +item.target.getAttribute('hpos');
				let yCoor = +item.target.getAttribute('vpos');
				
				let hooverTop = 0 + (+yCoor * editorOptions['zoomFactor']);
				let hooverLeft = 0 + (+xCoor * editorOptions['zoomFactor']);
				
				$("#hooverCraft").css({
					'position' : 'absolute',	
					'width' : +item.target.getAttribute('width') * editorOptions['zoomFactor'] + 'px',
					'height' : +item.target.getAttribute('height') * editorOptions['zoomFactor'] + 'px',
					'left' : hooverLeft + 'px',
					'top' : hooverTop + 'px',
					'backgroundColor' : 'rgba(255, 0, 0, 0.2)'
				});
			}
		}
	},
	//TODO implementing as filter would be nicer
	renderAllChangesHighlighting : function(changes) {
		/*_.each(changes, function(object) {
			$("span[hpos=" + object.get('HPOS') + "][vpos=" + object.get('VPOS') + "]:contains(" + object.get('after') + ")").css({
				'border' : 'red solid 1px'
			});
			
		}, this);*/
		if (editorOptions['facsimileVisibility'])	{
			_.each(changes, function(object) {
				let hooverTop = 0 + (+object.get('VPOS') * editorOptions['zoomFactor']);
				let hooverLeft = 0 + (+object.get('HPOS') * editorOptions['zoomFactor']);
				
				let width = 0 + (+object.get('WIDTH') * editorOptions['zoomFactor']);
				let height = 0 + (+object.get('HEIGHT') * editorOptions['zoomFactor']);
				
				
				$("#scanImage").append('<div id="change_' + object.get('HPOS') + '_' + object.get('VPOS') + '" style="position: absolute; ' + 'left: ' + hooverLeft + 'px' + '; top: ' +  + hooverTop + 'px' + '; width: ' + width + 'px' + '; height: ' + height + 'px' + '; border: red solid 1px; ' + '"></div>');
			}, this);
		}
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
					changesHTML += '<li class="list-group-item" HPOS="' + object.get('HPOS') + '" VPOS="' + object.get('VPOS') + '" WIDTH="' + object.get('WIDTH') + '" HEIGHT="' + object.get('HEIGHT') + '">' +
					object.get('Page').updatedByUser.name + 
				  	' changed ' + object.get('before') + ' to ' + object.get('after') +
				  	' at ' + object.get('Page').updatedAt +
				  '</li>';
				} else {
					changesHTML += '<li class="list-group-item">' +
					object.get('Page').updatedByUser.name + 
				  	' changed ' + object.get('before') + ' to ' + object.get('after') +
				  	' on <a href="./editor/#documents/' + object.get('Page').documentID + '/pages/' +  + object.get('Page').number + '">page <i>' +object.get('Page').number + ' of document</i></a>' +
				  	' at ' + object.get('Page').updatedAt +
				  '</li>';
				}
				
			}, this);
			
		
			
			changesHTML += '</ul>';
			
			self.$el.append(changesHTML);
			
			if (scope == 'page') {
				self.renderAllChangesHighlighting(differenceCollection.models);
			}
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