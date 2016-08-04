window.Role = Master.extend({
	urlRoot : apiHost + "authentication/roles",

	defaults : {
		"id" : null,
		"name" : ""
	}
});

window.RoleCollection = MasterCollection.extend({
	model : Role,
	url : apiHost + "authentication/roles"
});