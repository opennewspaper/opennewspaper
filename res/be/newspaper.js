var path = window.location.pathname;
path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"

function getViewportWidth() {
	// source: http://andylangton.co.uk/articles/javascript/get-viewport-size-javascript/
	if (typeof document.innerWidth != 'undefined') {
	  // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
	  return document.innerWidth;
	}
	if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
	  // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
	  return document.documentElement.clientWidth;
	}
	// older versions of IE
	return document.getElementsByTagName('body')[0].clientWidth;
}
function getViewportHeight() {
//source: http://andylangton.co.uk/articles/javascript/get-viewport-size-javascript/
	if (typeof document.innerHeight != 'undefined') {
	  // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
	  return document.innerHeight;
	} 
	if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientHeight != 'undefined' && document.documentElement.clientHeight != 0) {
	  // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
	  return document.documentElement.clientHeight;
	} 
	// older versions of IE
	return document.getElementsByTagName('body')[0].clientHeight;
}

function splitAtPipe(str, pos){
	str = str + '';
	part = str.split("|");
	if (part.length > pos) {
		return part[pos];
	}
	return false;
}




function extract_querystring(querystring, param) {
	querystring = unescape(querystring);
	if (querystring.substring(0, 1) == '?' || querystring.substring(0, 1) == '&') {
		querystring = querystring.substring(1);
	}
	var p = querystring.split('&'); // split querystring
	for (var i = 0; i < p.length; i++) {
		var pos = p[i].indexOf('=');
		if (pos > 0) {
			if (p[i].substring(0, pos) == param) {
				return p[i].substring(pos + 1); // return value of param
			}
		}
	}
	return ''; // no hit found
}
