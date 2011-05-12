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

// used for creating new extras (select box options: [class name]|[sysfolder uid])
// \param str String with Pipes, example: "abc|def|ghi"
// \param pos Number of element to be extracted (starting with 0); false if this elemet does not exist
function splitAtPipe(str, pos){
	str = str + '';
	part = str.split("|");
	if (part.length > pos) {
		return part[pos];
	}
	return false;
}


/**
 * @param param Param to search for
 * @param unescaped Boolean: if the querytsring should be unescaped or not
 * @return Value of the given param in the querystring
 */
function extractQuerystringDirect(param, unescaped) {
	return extract_querystring(window.location.search, param, unescaped);
}
/**
 * @param querystring Querystring to be searched
 * @param param Param to search for
 * @param unescaped Boolean: if the querytsring should be unescaped or not (default: true)
 * @param escapeParam Boolean if param should be escaped (default: false)
 * @return Value of the given param in the given querystring
 */
function extract_querystring(querystring, param, unescaped, escapeParam) {

	if (unescaped == 'undefined' || unescaped == true) {
		querystring = unescape(querystring); // true is default
	}

	if (escapeParam == true) {
		param = escape(param); // escape param
	}

	if (querystring.substring(0, 1) == '?') {
		querystring = querystring.substring(1);
	}
//alert('q: ' + querystring);
//alert('p: ' + param);
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



// http://javascript.about.com/library/bladdslash.htm
function addslashes(str) {
	str=str.replace(/\\/g,'\\\\');
	str=str.replace(/\'/g,'\\\'');
	str=str.replace(/\"/g,'\\"');
	str=str.replace(/\0/g,'\\0');
	return str;
}
function stripslashes(str) {
	str=str.replace(/\\'/g,'\'');
	str=str.replace(/\\"/g,'"');
	str=str.replace(/\\0/g,'\0');
	str=str.replace(/\\\\/g,'\\');
	return str;
}

function hscQuotes(str) {
	return str.replace(/"/g, "&quot;");
}


//if left = 1 string left of pipe is returned, else right part is returned
function splitParam(param, left, separator) {
	if (left != 1) {
		left = 0;
	}
	p = param.indexOf(separator);
	if (p < 1) return '';
	if (left == 1) {
		return param.substring(0, p);
	} else {
		return param.substring(p+1);
	}
}

function splitParamAtPipe(param, left) {
	return splitParam(param, left, '|');
}