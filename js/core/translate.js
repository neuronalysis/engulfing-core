var language = Cookie.get('userLanguage');
if (language == null) language = 'en';
var words = {
		en: {
			name: 'Name',
			//name: 'Ontology Name',
			owned_by: 'Owner',
			isFinal: 'Final',
			isPrivate: 'Private',
			isIdentifier: 'Identifier',
			defaultValue: 'Default Value',
			validationRegularExpression: 'Validation Rule',
			lexeme: 'Lexeme',
			type: 'Type',
			length: 'Length',
			language: 'Language'
		},
		de: {
			isPrivate: 'Geschuetzt',
			language: 'Sprache'
		}
	
};
function isAuthorizedField(objectName, fieldName, roleID) {
	if (roleID === "1") {
		return true;
	}
	/*if (objects[objectName] !== undefined) {
		if (objects[objectName][roleID] !== undefined) {
			return true;
		} else if (objects[objectName][99] !== undefined) {
			return true;
		}
	}*/
	
	return false;
}
function isAuthorized(objectName, roleID) {
	if (objects[objectName] !== undefined) {
		if (objects[objectName][roleID] !== undefined) {
			return true;
		} else if (objects[objectName][99] !== undefined) {
			return true;
		}
	}
	
	return false;
}
function getWordLike(like) {
	if (words[language][like]) return words[language][like];
	if (words.en[like]) return words.en[like];
	
	if (like) {
		if (like.substring(like.indexOf("_")+1) == "name") {
			return like.substring(0, like.indexOf("_"));
		} else {
			like = like.substring(like.indexOf("_")+1);
		}
		if (words[language][like]) return words[language][like];
		if (words.en[like]) return words.en[like];
		
		return like;
	}
	
	return null;
}
function getPlural(singular) {
	if (singular) {
		var trans = getWordLike(singular);
		
		if (trans === "Index") return "Indices";
		
 		return trans.pluralize();
	}
	
	return null;
}
function getOntology(objectName) {
	var objName = '';
	objName = objName + objectName;
	objName = objName.toLowerCase();
	var ontologyShortName = "km";
	if (objName == "lexeme" || objName == "word") {
		ontologyShortName = "nlp";
	} else if (objName == "datamart" || objName == "datamartservice") {
		ontologyShortName = "dwh";
	} else if (objName == "dataservice" || objName == "dataprovider" || objName == "datasource" || objName == "importservice") {
		ontologyShortName = "edi";
	} else if (objName == "role" || objName == "user" || objName == "owner") {
		ontologyShortName = "usermanagement";
	} else if (objName == "instrument" || objName == "indicator" || objName == "release" || objName == "impactfunction") {
		ontologyShortName = "neuronalysis";
	} else if (objName == "course" || objName == "module" || objName == "courseevent") {
		ontologyShortName = "lms";
	} else if (objName == "pillowcase" || objName == "pillowfilling") {
		ontologyShortName = "kissenstern";
	}
	
	return ontologyShortName;
}
function getSingular(plural) {
	if (plural) {
		var trans = getWordLike(plural);
		
		return trans.singularize();
	}
	
	return null;
}
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
function lowercaseFirstLetter(string) {
    return string.charAt(0).toLowerCase() + string.slice(1);
}
function getURLParameter(name) {
	return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null
}
function getSiteMapTitle() {
	for (var i=0; i < sitemap.urls.length; i++) {
		if (location.pathname.indexOf(sitemap.urls[i].url) > -1) {
			return sitemap.urls[i].title;
		}
	}
	
	return null;
}
Date.prototype.format = function() {
	var todaysDate = new Date();
	
	var formattedDate = "";
	
	var yyyy = this.getFullYear().toString();
	var mm = (this.getMonth()+1).toString();
	var dd  = this.getDate().toString();
	
	if(this.toDateString() == todaysDate.toDateString()) {
		var hours  = this.getHours().toString();
		var minutes  = this.getMinutes().toString();
		
		if (hours < 10) hours = "0" + hours;
		
		formattedDate = hours + ":" + minutes;
	} else {
		formattedDate = yyyy + "-" + (mm[1]?mm:"0"+mm[0]) + "-" + (dd[1]?dd:"0"+dd[0]);
	}
	   
	return formattedDate;
};
String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.split(search).join(replacement);
};