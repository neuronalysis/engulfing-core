var FileUploadView = InputView.extend({
	initialize : function(options) {
		this.template = _.template(tpl.get('components/input_file'));
		
		this.labelName = this.field;
		
		this.withLabel = false;
		
		if (options.withCell) {
			this.withCell = options.withCell;
		} else {
			this.withCell = false;
		}
		
		if (options.referencingObject) {
			this.referencingObject = options.referencingObject;
		} else {
			this.referencingObject = false;
		}
	},
	render : function() {
		if (accessMode == "edit") {
			var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "field_value":  this.model.get('name'), "withCell" : this.withCell, "withLabel" : this.withLabel, "labelName" : this.labelName};
			
			this.$el.html(this.template(data));
			
			if (this.model.isConcrete()) {
				if (this.model.isNew()) {
					var uploadUrl = 'http://localhost.ontologydriven/api/edi/uploadfile?ontologyClassName=' + this.field + '&referencingOntologyClassName=' + this.referencingObject.type + '&referencingEntityID=' + this.referencingObject.get('id');
					
					this.$("#input-1").fileinput({
						showUpload:true, 
						previewFileType:'any', 
						uploadUrl: uploadUrl,
						autoReplace: true,
						maxFileCount: 1,
						overwriteInitial: false,
						maxFileSize: 28000
					});
				} else {
					var uploadUrl = 'http://localhost.ontologydriven/api/edi/uploadfile?ontologyClassName=' + this.field + '&entityID=' + this.model.get('id');
					var downloadUrl = 'http://localhost.ontologydriven/api/edi/downloadfile?ontologyClassName=' + this.field + '&entityID=' + this.model.get('id');
					var deleteUrl = 'http://localhost.ontologydriven/api/edi/deletefile?ontologyClassName=' + this.field + '&entityID=' + this.model.get('id');
					
					// custom footer template for the scenario
					// the custom tags are in braces
					var footerTemplate = '<div class="file-thumbnail-footer">\n' +
					'   <div style="margin:5px 0">\n' +
					'       <input class="kv-input kv-new form-control input-sm text-center {TAG_CSS_NEW}" value="{caption}" placeholder="Enter caption...">\n' +
					'       <input class="kv-input kv-init form-control input-sm text-center {TAG_CSS_INIT}" value="{TAG_VALUE}" placeholder="Enter caption...">\n' +
					'   </div>\n' +
					'   {size}\n' +
					'   {actions}\n' +
					'</div>';
					
					this.$("#input-1").fileinput({
					    uploadUrl: uploadUrl,
					    uploadAsync: false,
					    maxFileCount: 1,
					    overwriteInitial: false,
					    layoutTemplates: {footer: footerTemplate, size: '<samp><small>({sizeText})</small></samp>'},
					    previewThumbTags: {
					        '{TAG_VALUE}': '',        // no value
					        '{TAG_CSS_NEW}': '',      // new thumbnail input
					        '{TAG_CSS_INIT}': 'hide'  // hide the initial input
					    },
					    initialPreview: [
					        "<img style='height:160px' src='"+ downloadUrl + "'>"
					    ],
					    initialPreviewConfig: [
					        {caption: this.model.get('name'), size: 327892, width: "120px", url: deleteUrl, key: this.model.get('id')} 
					    ],
					    initialPreviewThumbTags: [
					        {'{TAG_VALUE}': 'City-1.jpg', '{TAG_CSS_NEW}': 'hide', '{TAG_CSS_INIT}': ''}
					    ],
					    uploadExtraData: function() {  // callback example
					        var out = {}, key, i = 0;
					        $('.kv-input:visible').each(function() {
					            $el = $(this);
					            key = $el.hasClass('kv-new') ? 'new_' + i : 'init_' + i;
					            out[key] = $el.val();
					            i++;
					        });
					        return out;
					    }
					});
				}
				
				
			} else {
				this.$("#input-1").fileinput({
					'showUpload':true,
					'previewFileType':'any',
					'uploadUrl': 'http://localhost.ontologydriven/api/edi/uploadfile?ontologyClassID=' + this.model.get('id')
					});
			}
		} else {
			if (this.model) {
				if (this.model.isConcrete()) {
					var downloadUrl = 'http://localhost.ontologydriven/api/edi/downloadfile?ontologyClassName=' + this.field + '&entityID=' + this.model.get('id');
				} else {
					var downloadUrl = 'http://localhost.ontologydriven/api/edi/downloadfile?ontologyClassID=' + this.model.get('id');
				}
				
				var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "field_value":  this.model.get('name'), "withCell" : this.withCell, "withLabel" : this.withLabel, "labelName" : this.labelName, "downloadUrl" : downloadUrl};
			} else  {
				var data = {"object_name": this.model.type.toLowerCase(), "field_name": this.field, "field_value": null, "withCell" : this.withCell, "withLabel" : this.withLabel, "labelName" : this.labelName, "downloadUrl" : null};
			}
			
			this.$el.html(this.template(data));
		}
		
		return this;
	}
});
		