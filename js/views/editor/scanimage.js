var ScanImageView = BaseView.extend({
	initialize : function() {
	},
	events : {
		"change" : "changeValue"
	},
	render : function() {
		this.$el.empty();
		
		let imgWidth = this.model.get('width').replace('px', '') * editorOptions['zoomFactor'];
		let imgHeight = this.model.get('height').replace('px', '') * editorOptions['zoomFactor'];
		
		this.$el.css({
			'width'         	: imgWidth + 'px',
			'height'        	: imgHeight + 'px',
			});

		this.$el.html('<img src="' + "/kokos/data/ocr/images/" + this.model.get('filePath') + '" width="' + imgWidth + 'px' + '" height="' + imgHeight + 'px' + '"></img>');

		return this;
	}
});
