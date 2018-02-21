window.Role = Master.extend({
	urlRoot : apiHost + "authentication/roles",

	defaults : {
		"id" : null,
		"name" : ""
	},
	enumeration : [ {
		id : "1",
		text : "Administrator"
	}, {
		id : "2",
		text : "Guest"
	}, {
		id : "4",
		text : "Supervisor"
	}, {
		id : "13",
		text : "Editor"
	}  ]
});

window.RoleCollection = MasterCollection.extend({
	model : Role,
	url : apiHost + "authentication/roles"
});