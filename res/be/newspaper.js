
// @todo: use NpTools.getPath() only !!!
var path = window.location.pathname;
path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"



/**
 * A collection of tools and utilities
 */
var NpTools = {
    param: [],

    /**
     * Gets the root path of the installation
     * @return Root path of the installation
     * @todo: will this always work??? well, no ... :-(
     */
    getPath: function() {
	    var path = window.location.pathname;
		return path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"
    },

    /**
     * Get path to newspaper directory in typo3conf/ext
     */
    getNewspaperPath: function() {
    	var path,
    		p;

    	var path = window.location.pathname;
    	if (p = path.indexOf("typo3conf/ext/newspaper")) {
    		return path.substring(0, p+23);
    	}
    	
// @todo: other cases are still missing ...

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
     * @param unescaped Boolean: if the querystring should be unescaped or not (default: true)
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
    },


// some newspaper string tools

	/**
	 * Renders a string representing the kicker and title of an article
	 * @param kicker Kicker of the article
	 * @param title Title of the article
	 * @return Title for an article
	 */
	assembleArticleTitle: function(kicker, title) {
    	if (!kicker && !title) {
    		return "---";
    	}

		var ret = "";
		if (kicker) {
			ret = kicker + ": ";
		}
		ret += title;
		return ret;
	}

};



/**
 * Newspaper backend function that are needed at various places
 */
var NpBackend = {
	param: [],

	/**
	 * Updates the selected template set for a given record
	 * @param table Table containing the record to be updated
	 * @param uid uid of the reocrd to be updated
	 * @param value Name of template set to be stored
	 * @return void
	 */
	storeTemplateSet: function(table, uid, value) {
		uid = parseInt(uid);
		var request = new top.Ajax.Request(
			path + "typo3conf/ext/newspaper/mod3/index.php", {
				method: 'get',
				parameters: "templateset_dropdown_store=1&table=" + table + "&uid=" + uid + "&value=" + value + "&no_cache=" + new Date().getTime(),
			}
		);
	},

	/**
	 * Checks if the textarea field exceeds maxLen. Displays countdown if countdownField is set
	 * @param field Reference to textarea
	 * @param maxLen maximum numbr of characters
	 * @param countdownField id of countField or empty
	 * @return void
	 */
	checkMaxLenTextarea: function(field, maxLen, countdownField) {
		if (field.value.length > maxLen) {
			field.value = field.value.substring(0, maxLen);
		}
		if (countdownField) {
			document.getElementById(countdownField).innerHTML = parseInt(maxLen - field.value.length);
		}
	},

	/**
	 * Opens the newspaper element browser
	 * Expects this.param["ElementBrowserUrl"], this.param["ElementBrowserWidth"], this.param["ElementBrowserHeight"] to be set
	 * @param params
	 * @param form_table
	 * @param form_field
	 * @param form_uid
	 */
	setFormValueOpenBrowser: function(params, form_table, form_field, form_uid) {
		browserWin = window.open(this.param["ElementBrowserUrl"], "Typo3WinBrowser", "height=" + this.param["ElementBrowserHeight"] + ",width=" + this.param["ElementBrowserWidth"] + ",status=0,menubar=0,resizable=1,scrollbars=1");
		browserWin.focus();
	},

	/**
	 * Opens the url in a preview popup
	 * @param url URL to open in the preview popup
	 * @return void
	 */
	showArticlePreview: function(url) {
		window.open(
			url,
			"preview",
			"width=800,height=500,left=100,top=100,resizable=yes,toolbar=no,location=no,scrollbars=yes"
		);
	},

	/**
	 * Add a new option to a select box
	 * @param selectboxObj DOM select box
	 * @param value Value to be stored in the select box
	 * @param label Label to be rendered in the select box
	 * @return true, if the option was added, flase if not (value has been on use already)
	 */
	addOption: function(selectboxObj, value, label) {
		// don't add if value is already in use
		for (var i = 0; i < selectboxObj.options.length; i++) {
			if (selectboxObj.options[i].value == value) {
				return false; // this option has been added to the list already
			}
		}

		// attach new option to select box
		selectboxObj.options[selectboxObj.length] = new Option(label, value, false, false);
		return true;
	},


	/**
	 * Remove all selected options from select box selectboxObj
	 * @param selectboxObj DOM select box
	 * @return true, if at least one option was removed, else false
	 */
	removeSelectedOptions: function(selectboxObj) {
		var found = false;
		for (var i = selectboxObj.options.length-1; i >= 0; i--) {
			if (selectboxObj.options[i].selected) {
				selectboxObj.options[i] = null;
				found = true;
			}
		}
		return found;
	},

	/**
	 * Remove all options from select box selectboxObj
	 * @param selectboxObj DOM select box
	 * @return void
	 */
	removeAllOptions: function(selectboxObj) {
		for (var i = selectboxObj.options.length-1; i >= 0; i--) {
			selectboxObj.options[i] = null;
		}
	},

	/**
	 * Show a semi-transparent layer with a spinner
	 * @return void
	 */
	showProgress: function() {
		if (!document.getElementById("NpBackendProgress")) {
			var layer = document.createElement("div");
			layer.setAttribute("id", "NpBackendProgress");
			layer.style.backgroundAttachment = "scroll";
			layer.style.backgroundColor = "rgba(255, 255, 255, 0.7)";
			layer.style.backgroundImage = "none";
			layer.style.backgroundPosition = "0 0";
			layer.style.backgroundRepeat = "repeat";
		    layer.style.position = "absolute";
			layer.style.top = 0;
			layer.style.bottom = 0;
		    layer.style.left = 0;
		    layer.style.right = 0;
		    layer.style.zIndex = 99;
		    layer.style.color = "#808080";
		    layer.style.textAlign = "center";
			layer.innerHTML = '<img style="padding-top:180px;" src="' + this.getSpinnerFile() + '" alt="" />';
			document.body.appendChild(layer);
		}
		document.getElementById("NpBackendProgress").style.display = "inline";
	},

	/**
	 * Hides the layer and spinner (see NpBackend.showProgress())
	 * @return void
	 */
	hideProgress: function() {
		if (!document.getElementById("NpBackendProgress")) {
			return; // I've got nothing to hide ;-)
		}
		document.getElementById("NpBackendProgress").style.display = "none";
	},

	/**
	 * Get path to spinner file
	 * @return Path to spinner file
	 */
	getSpinnerFile: function() {
//console.log(document.location.href);
        if (document.location.href.indexOf('typo3conf/ext/newspaper/mod2/res/returnUrl.html&edit[tx_newspaper_article]') >= 0) {
            // Article backend
            return "../typo3conf/ext/newspaper/res/be/css/move-spinner.gif";
        }
        if (document.location.href.indexOf('M=txnewspaperMmain_txnewspaperM9') >= 0) {
            // Section article list module
			return "../typo3conf/ext/newspaper/res/be/css/move-spinner.gif"; //
		}
		if (document.location.href.indexOf('tx_newsapper_mod7[controller]=singleplacement') >= 0) {
            // Placement module (?)
			return "move-spinner.gif";
		}
        if (document.location.href.indexOf('typo3/backend.php') >= 0) {
            // ???
            return "../typo3conf/ext/newspaper/res/be/css/move-spinner.gif";
        }
		return "../res/be/css/move-spinner.gif"; // default
	},


    /**
     * Call dependency tree via AJAX
     * Just the call gets triggered, the response is ignored
     * @todo: Check if path is always working correctly
     * @param type String in 'tag', 'extra'; see callDepTree() in mod1/index.php
     * @param uid uid of record passed in type
     */
    callDepTree: function(type, uid) {
        var request = new Ajax.Request(
       		path + "/mod1/index.php", {
       			method: 'get',
                parameters: "tx_newspaper_mod1[ajaxController]=depTree&tx_newspaper_mod1[type]=" + type + "&tx_newspaper_mod1[uid]=" + parseInt(uid)
       		}
       	);
    },

    /**
     * Call dependency tree for a Tag via AJAX
     * Just the call gets triggered, the response is ignored
     * @param uid uid of Tag
     */
    callDepTreeForTag: function(uid) {
        this.callDepTree('tag', uid);
    },

    /**
     * Call dependency tree for an Extra via AJAX
     * Just the call gets triggered, the response is ignored
     * @param uid uid of Extra
     */
    callDepTreeForExtra: function(uid) {
        this.callDepTree('extra', uid);
    }

}



/**
 * Javascript function s for activating and deleting pages and pagezones in the
 * section backend.
 */
var NpPagePagetype = {
	param: [],

	/**
	 * AJAX call: Activate page type, displays a spinner
	 * @param section_id Section uid
	 * @param pagetype_uid Page type uid
	 * @return void
	 */
    activatePageType: function(section_id, pagetype_id) {
    	var request = new Ajax.Request(
    		path + "typo3conf/ext/newspaper/mod1/index.php", {
    			method: 'get',
    			parameters: "activate_page_type&param=[section]" + section_id + "|[pagetype]" + pagetype_id + "&no_cache=" + new Date().getTime(),
    			onSuccess: this.updatePageTypePageZoneType
    		}
    	);
    	document.getElementById('pagetype_pagezonetype').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
    },

	/**
	 * AJAX call: Delete page (after confirmation check)
	 * @param section_id Section uid
	 * @param page_id Page uid
	 * @param message Confirmation message
	 * @return void
	 */
    deletePage: function(section_id, page_id, message) {

		if (message == undefined) {
			alert('Illegal function call');
			return;
		}

		// user must confirm that he knows what he's doing
		if (!confirm(message)) {
			return;
		}

		/// ajax call: delete page
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "delete_page&param=[section]" + section_id + "|[page]" + page_id + "&no_cache=" + new Date().getTime(),
					onSuccess: this.updatePageTypePageZoneType
				}
		);
		document.getElementById('pagetype_pagezonetype').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	},

	/**
	 * AJAX call: Activate pagezone type, displays a spinner
	 * @param section_id Section uid
	 * @param page_id Page uid
	 * @param pagezone_type Pagezone type uid
	 * @return void
	 */
	activatePageZoneType: function(section_id, page_id, pagezone_type) {
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php", {
				method: 'get',
				parameters: "activate_pagezone_type&param=[section]" + section_id + "|[page]" + page_id + "|[pagezonetype]" + pagezone_type + "&no_cache=" + new Date().getTime(),
				onSuccess: this.updatePageTypePageZoneType
			}
		);
		document.getElementById('pagetype_pagezonetype').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	},

	/**
	 * AJAX call: Delete pagezone (after confirmation check)
	 * @param section_id Section uid
	 * @param page_id Page uid
	 * @param pagezone_id Pagezone uid
	 * @param message Confirmation message
	 * @return void
	 */
	deletePageZone: function(section_id, page_id, pagezone_id, message) {

		if (message == undefined) {
			alert('Illegal function call');
			return;
		}

		// user must confirm that he knows what he's doing
		if (!confirm(message)) return;

		/// ajax call: delete page zone
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "delete_pagezone&param=[section]" + section_id + "|[page]" + page_id + "|[pagezone]" + pagezone_id + "&no_cache=" + new Date().getTime(),
					onSuccess: this.updatePageTypePageZoneType
				}
		);
		document.getElementById('pagetype_pagezonetype').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	},

	/// one update function for all ajax calls
    /**
     * AJAX onSuccess function for all AJAX call in section backend
     * @param request AJAX response
     * @return void
     */
    updatePageTypePageZoneType: function(request) {
		var json = request.responseText.evalJSON(true);
		document.getElementById('pagetype_pagezonetype').innerHTML = json.html;
	}

}


