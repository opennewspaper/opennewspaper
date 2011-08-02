
// @todo: use NpTools.getPath() only !!! xyz
var path = window.location.pathname;
path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"


var NpTools =  {
    params: [],

    /**
     * Gets the root path of the installation
     * @return Root path of the installation
     * @todo: will this always work???
     */
    getPath: function() {
	    var path = window.location.pathname;
		return path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"
    },


// some gui tools

    /**
     * @return Viewport width
     */
    getViewportWidth: function() {
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
    },

    /**
     * @return Viewport height
     */
    getViewportHeight: function() {
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
    },


// some string processing tools

    /**
     * Replaces " with &quot;
     * @param str String to be processed
     * @return String with " replaced by &quot;
     */
    hscQuotes: function(str) {
    	return str.replace(/"/g, "&quot;");
    },

	/**
	 * Replaces " with %22
	 * @param str String to be processed
	 * @return
	 */
    escapeQuotes: function(str) {
    	return str.replace(/"/g, "%22");
    },

    /**
     * Gets a part of a string split by the pipe charcter
     * @param str String with Pipes, example: "abc|def|ghi"
     * @param pos Number of element to be extracted (starting with 0); false if this elemet does not exist
     * @return Sub string devided by pipe character. pos determines the number of splitted sub strings to be returned
     */
    splitAtPipe: function(str, pos) {
    	str = str + '';
    	part = str.split("|");
    	if (part.length > pos) {
    		return part[pos];
    	}
    	return false;
    },

 // http://javascript.about.com/library/bladdslash.htm
    addslashes: function(str) {
    	str=str.replace(/\\/g,'\\\\');
    	str=str.replace(/\'/g,'\\\'');
    	str=str.replace(/\"/g,'\\"');
    	str=str.replace(/\0/g,'\\0');
    	return str;
    },
    stripslashes: function(str) {
    	str=str.replace(/\\'/g,'\'');
    	str=str.replace(/\\"/g,'"');
    	str=str.replace(/\\0/g,'\0');
    	str=str.replace(/\\\\/g,'\\');
    	return str;
    },

    /**
    * Gets a sub string depeding on separator and param left
    * @param param     String to be processed
    * @param left      If set to 1, the part left of the separator is returned, right part else
    * @param separator Separator for splitting the string
    * @return Part left or right (depending on param left) of the separator
    */
    splitParam: function(param, left, separator) {
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
    },

    /**
     * Gets a sub string depeding param left and the separator "|"
     * @param param     String to be processed
     * @param left      If set to 1, the part left of the separator is returned, right part else
     * @return Part left or right (depending on param left) of the pipe character
     */
    splitParamAtPipe: function(param, left) {
    	return this.splitParam(param, left, '|');
    },


    /**
     * @param param Param to search for
     * @param unescaped Boolean: if the querystring should be unescaped or not
     * @return Value of the given param in the querystring
     */
    extractQuerystringDirect: function(param, unescaped) {
    	return this.extract_querystring(window.location.search, param, unescaped);
    },
    /**
     * This function should be called only, if the querystring to be parsed is NOT window.location.search
     * @param querystring Querystring to be searched
     * @param param Param to search for
     * @param unescaped Boolean: if the querytsring should be unescaped or not (default: true)
     * @param escapeParam Boolean if param should be escaped (default: false)
     * @return Value of the given param in the given querystring
     */
    extract_querystring: function(querystring, param, unescaped, escapeParam) {

    	if (unescaped == 'undefined' || unescaped == true) {
    		querystring = unescape(querystring); // true is default
    	}

    	if (escapeParam == true) {
    		param = escape(param); // escape param
    	}

    	if (querystring.substring(0, 1) == '?') {
    		querystring = querystring.substring(1); // cut leading "?"
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

};
