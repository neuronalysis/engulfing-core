window.Information = Master.extend({
	url : apiHost + "extraction/information",
	defaults : {
		"id": null
	},
	relations : [ ]
});

window.InformationCollection = MasterCollection.extend({
	model : Information,
	url : apiHost + "extraction/information"
});