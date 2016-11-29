var referrer = document.referrer;
//console.log("href: " + window.location.href + "; referrer: " + referrer);
if (window.location.href.indexOf("localhost") === -1) {
	if (referrer.indexOf("generated") === -1) {
		var kmapiHost = "http://www.ontologydriven.com/api/";
		var apiHost = "http://www.ontologydriven.com/api/";
	} else {
		var kmapiHost = "http://www.ontologydriven.com/api/";
		var apiHost = "http://www.ontologydriven.com/generated/api/";
	}
	
	var odBase = "http://www.ontologydriven.com/";
	var engulfingBase = "http://www.engulfing.com/";
	var neuroBase = "http://www.neuronalysis.com/";
} else {
	if (referrer.indexOf("generated") === -1) {
		var kmapiHost = "http://localhost.ontologydriven/api/";
		var apiHost = "http://localhost.ontologydriven/api/";
	} else {
		var kmapiHost = "http://localhost.ontologydriven/api/";
		var apiHost = "http://localhost.ontologydriven/api/";
	}
	
	var odBase = "http://localhost.ontologydriven/";
	var engulfingBase = "http://localhost.engulfing/";
	var neuroBase = "http://localhost.generated/neuronalysis/";
	
	
}


//var apiHost = "http://datamart.services/api/";