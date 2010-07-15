
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

var tabManagement =  {
    tabIds: [],
    activeTabClass: 'extra_tab_act',

    initialize: function() {
//        tabManagement.confirmMessage = confirmationMessage;
        tabManagement._hideAllTabs();
    },

    show: function(tabId, params) {
        var tableName = tabId.split('_');
        var id = tableName.pop();
        var isExtraTab = !isNaN(id); //last part of string is concrete id when its an extra tab

        tableName = tableName.join('_');
        var tab_id = isExtraTab ? tableName + '_' + id : tabId; //if it's no extra the passed in tabid was a real div#id

        tabManagement._hideAllTabs();

        //after an ajax reload the iframe must be loaded again but tabIds already contains the current tab_id
        //therefore check for empty div.
        // isExtraTab is true when the current tab is an extra and therefore the iframe must be loaded.
        if( ($(tab_id).innerHTML == "") && isExtraTab) {
            $(tab_id).innerHTML='<iframe height="840px" width="100%" id="iframe_'+id+'" src="alt_doc.php?edit['+tableName+']['+id+']=edit""></iframe>';

            //after an ajax reload the tab_id is already inside the list
            if(!tabManagement.tabIds.include(tab_id)) {
                tabManagement.tabIds.push(tab_id);
            }
        }

        tabManagement.markActiveTab(tab_id);
        $('lastTab').value = tab_id;
        $(tab_id).show();
    },

    markActiveTab: function(tab_id) {
        $('extras').select('.' + tabManagement.activeTabClass).each(function(anchor) {
            anchor.removeClassName(tabManagement.activeTabClass);
        }, this);
        $('tab_'+ tab_id).select('a').each(function(a) {a.addClassName(tabManagement.activeTabClass)}, this);
    },

    /**
     *
     * @param saveInput savedok or saveandclosedok
     */
    submitTabs: function(saveInput) {
        tabManagement.tabIds.each(function(tabId) {
            var iframeId = tabId.split('_').pop();
            var frameName = 'iframe_'+ iframeId ;
            var iframeDok = $(frameName).contentDocument;
            if(iframeDok == null) {
                alert("No document for " + frameName + " found");
            }

            //typo3 needs these coordinates somehow to properly save the article.
//                saveInput.name = '_saveandclosedok';
            ['.x', '.y'].each(function(suffix) {
                var saveDokInput = new Element('input', {type: 'hidden', name: saveInput.name + suffix, value: 1});
                iframeDok.forms[0].appendChild(saveDokInput);
            });
            $A(iframeDok.getElementsByName('doSave')).each(function(elem) { elem.value = 1 });
            iframeDok.forms[0].submit();
            //                var frameForm = iframeDok.forms[0];
////                alert(frameForm.action);
////                alert(Form.serialize(frameForm));
//                new Ajax.Request(frameForm.action, {
//                    parameters: Form.serialize(frameForm)
//                });
        });

        var tabsAreSaving = true;
        var count = 0;
        var keepAsking = false;
//            while(tabsAreSaving) {
//                //var openTabs = tabManagement.tabIds.findAll(function(it) { return top.window.frames[$(it).id].document.forms.length > 0}).size();
//                var openTabs = true;
//                for(var j = 0; j < tabManagement.tabIds.size(); j++) {
//                    try {
//                        if(keepAsking)
//                            alert(top.window.frames[tabManagement.tabIds[j]].document.body.id);
//                        tabsAreSaving &= top.window.frames[tabManagement.tabIds[j]].document.body.id == "";
//                    } catch(e) {
//                        openTabs = false;
//                        if(keepAsking)
//                            alert(tabManagement.tabIds[j] + " " + e);
//                    }
//                }
//                if(keepAsking)
//                    keepAsking = confirm("open tabs " + openTabs + " still saving..." + tabsAreSaving);
////                tabsAreSaving = !openTabs;
//                if(count > 10000) {
//                    alert('breaking out');
//                    break;
//                }
//                count++;
//            }
        //alert("open tabs " + openTabs);
        return true;
    },

    /**
     * hide all tabs, they must have a css-class called .extra_tab
     */
    _hideAllTabs: function() {
        $$('.extra_tab').each(function(div){ div.hide();});
    },

    isDirty: function() {
        return tabManagement.tabIds.size() > 0;
    },

//    askUserContinueIfDirty: function() {
//        var allowSubmit = true;
//        if(tabManagement.isDirty()) {
//            allowSubmit = confirm(tabManagement.confirmMessage);
//        }
//        return allowSubmit;
//    },

    removeTab: function(extra_class, extra_uid) {
        tabManagement.tabIds = tabManagement.tabIds.without(extra_class + '_' + extra_uid);
    },

    clearTabCache: function() {
        tabManagement.tabIds = [];
    }

};
