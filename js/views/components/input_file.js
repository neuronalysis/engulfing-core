var FileUploadView = InputView.extend({
	initialize : function() {
		this.template = _.template(tpl.get('components/input_file'));
	},
	render : function() {
		this.$el.html(this.template());
		
		this.$("#input-1").fileinput({'showUpload':true, 'previewFileType':'any', 'uploadUrl': 'http://localhost.ontologydriven/api/edi/uploadfile?ontologyClassID=' + this.model.get('id')});
		
		return this;
	}
});
		