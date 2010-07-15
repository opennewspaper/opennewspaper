//{literal}
// this script assumes prototype (ajax) to be available







// add javascript and css if not available (backend called in popup WITHOUT typo3 frameset where these scripts are loaded to)
var t3BackendObject = getTypo3BackendObject();
if (t3BackendObject != top) {
	loadJsCssFile(t3BackendObject.path + "typo3conf/ext/newspaper/contrib/subModal/newspaper_subModal.js", "js");
	loadJsCssFile(t3BackendObject.path + "typo3conf/ext/newspaper/res/be/newspaper.js", "js");
	loadJsCssFile(t3BackendObject.path + "typo3conf/ext/newspaper/contrib/subModal/subModal.css", "css");
    loadJsCssFile(t3BackendObject.path + "typo3/contrib/scriptaculous/scriptaculous.js", "js", "load=builder,effects,controls,dragdrop");
    loadJsCssFile(t3BackendObject.path + "typo3conf/ext/newspaper/res/be/autocomplete.css", "css");
}

//http://www.javascriptkit.com/javatutors/loadjavascriptcss.shtml
function loadJsCssFile(filename, filetype, param) {
	if (filetype == "js") { //if filename is a external JavaScript file
		var fileref = document.createElement('script');
		fileref.setAttribute("type", "text/javascript");
        if(param) {
            filename = filename + '?' + param; 
        }
        fileref.setAttribute("src", filename );
	} else if (filetype == "css") { //if filename is an external CSS file
		var fileref = document.createElement("link");
		fileref.setAttribute("rel", "stylesheet");
		fileref.setAttribute("type", "text/css");
		fileref.setAttribute("href", filename);
	}
	if (typeof fileref != "undefined") {
		document.getElementsByTagName("head")[0].appendChild(fileref);
	}
}


/// utility functions //////////////////////////////////////////////////////////

	// returns object containing getViewportWidth() etc. functions (or returns false if not available)
	function getTypo3BackendObject() {
//top.console.log('getTypo3BackendObject()');
//top.console.log(typeof top.getViewportWidth);
//alert('+++');
		if (typeof top.getViewportWidth == 'function') {
			return top; // called in "normal" typo3 backend
		}
		if (typeof opener.top.getViewportWidth == 'function') {
			return opener.top; // called in standalone popup
		}
		return false; // no reference could be found
	}
	

	/// returns the reload type for ajax call depending on the flag "is_concrete_article"
	function get_onSuccess_function(is_concrete_article) {
		if (is_concrete_article)
			return 'response_in_article';
		else
			return 'reload_page';
	}
	/// returns the "processing spinner" type for ajax call depending on the flag "is_concrete_article"
	function get_onCreate_function(is_concrete_article) {
		if (is_concrete_article)
			return 'processing_in_article';
		else
			return 'processing_page';
	}

/// \todo: use this function in all ajax calls
	function modify_no_cache_param(url) {
		/// \todo: currently no_cache param are ADDED, must be REPLACED
		return url + "&no_cache=" + new Date().getTime();
	}

	
	/// "processing spinner" over whole page
	function processing_page() {
		img = document.createElement('img');
		img_src = document.createAttribute('src');
		img_src.nodeValue = top.path + 'typo3/gfx/spinner.gif';
		img.setAttributeNode(img_src);
		layer_style = document.createAttribute('style');
		layer_style.nodeValue = 'color: red; position: absolute; z-index: 99; text-align: center; background: rgba(208, 208, 208, 0.8) none repeat scroll 0 0; top: 0; bottom: 0; left: 0; right: 0; padding-top: 33%;';
		layer = document.createElement('div');
		layer.setAttributeNode(layer_style);
		layer.appendChild(img);
		self.document.getElementsByTagName('body')[0].appendChild(layer);
	}
	
	function processing_in_article() {
		document.getElementById('extras').innerHTML = '<img src="' + top.path + 'typo3/gfx/spinner.gif"/>';
	}

	
	/// direct reload (no popup/modalbox involved ...)
	function reload_page(data) {
		if (data.responseText == '') {
			// empty string, so no error message
			self.location.href = modify_no_cache_param(self.location.href);
		} else {
			// display error message
			self.document.getElementsByTagName('body')[0].innerHTML = data.responseText;
		}
	}

	/// just display the ajax response as extra list in concrete article
	function response_in_article(data) {
		document.getElementById('extras').innerHTML = data.responseText;
        tabManagement.clearTabCache();
	}
	
	/// the list of extras has to be reloaded from the server (needed for modal box or saveField ajax calls)
	function reload_in_article(pz_uid) {
		if (t3BackendObject == top) {
			ref = top.content;
		} else {
			ref = top;
		}
		var request = new top.Ajax.Request(
			t3BackendObject.path + "typo3conf/ext/newspaper/mod3/index.php",
			{
				method: 'get',
				parameters: "reload_extra_in_concrete_article=1&pz_uid=" + pz_uid + "&no_cache=" + new Date().getTime(),
				onCreate: ref.processing_in_article,
				onSuccess: response_in_article
			}
		);
	}	
		
	
	
	
	
/// functions for placement module only ////////////////////////////////////////
	
	/// toggle checkbox "Show levels above"
	function toggle_show_levels_above(checked) {
		var request = new top.Ajax.Request(
				top.path + "typo3conf/ext/newspaper/mod3/index.php",
				{
				method: 'get',
				parameters: "toggle_show_levels_above=1&checked=" + checked + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			}
		);
	}	
	
	/// toggle checkbox "Show visible extras only"
	function toggle_show_visible_only(checked) {
		var request = new top.Ajax.Request(
				top.path + "typo3conf/ext/newspaper/mod3/index.php",
				{
				method: 'get',
				parameters: "toggle_show_visible_only=1&checked=" + checked + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			}
		);
	}		

	
	
/// functions for placement pagezone_page //////////////////////////////////////
	
	
	
	
	
/// functions for placement (default) article //////////////////////////////////	
		
	/// AJAX call: create extra on article, started by shortcut link
	function extra_shortcut_create(article_uid, extra_class, extra_uid, paragraph) {
		var request = new top.Ajax.Request(
			top.path + "typo3conf/ext/newspaper/mod3/index.php",
			{
				method: 'get',
				parameters: "extra_shortcut_create=1&article_uid=" + article_uid + "&extra_class=" + extra_class + "&extra_uid=" + extra_uid + "&paragraph=" + paragraph + "&no_cache=" + new Date().getTime(),
				onCreate: eval(get_onCreate_function(1)),
				onSuccess: function(transport) {
                    var data = transport.responseText.evalJSON();
                    $('extras').innerHTML = data.htmlContent;
                    tabManagement.clearTabCache();
                    tabManagement.show(extra_class + '_' + data.extra_uid);

                }
			}
		);
	}
		
		
		

	
/// functions for placement article AND concrete article ///////////////////////
	
	/// AJAX call: delete extra on pagezone_page or article
	function extra_delete(pz_uid, extra_uid, message, is_concrete_article, extra_class) {
		if (!confirm(message)) return; // user must confirm that he knows what he's doing
		var request = new top.Ajax.Request(
			top.path + "typo3conf/ext/newspaper/mod3/index.php",
			{
				method: 'get',
				parameters: "extra_delete=1&pz_uid=" + pz_uid + "&extra_uid=" + extra_uid + "&no_cache=" + new Date().getTime(),
				onCreate: eval(get_onCreate_function(is_concrete_article)),
				onSuccess: eval(get_onSuccess_function(is_concrete_article))
			}
		);
	}
	
	/// AJAX call: move extra on pagezone_page or article
	function extra_move_after(origin_uid, pz_uid, extra_uid, is_concrete_article) {
//alert(top.path + "typo3conf/ext/newspaper/mod3/index.php");
	var request = new top.Ajax.Request(
				top.path + "typo3conf/ext/newspaper/mod3/index.php",
				{
				method: 'get',
				parameters: "extra_move_after=1&origin_uid=" + origin_uid + "&pz_uid=" + pz_uid + "&extra_uid=" + extra_uid + "&no_cache=" + new Date().getTime(),
				onCreate: eval(get_onCreate_function(is_concrete_article)),
				onSuccess: eval(get_onSuccess_function(is_concrete_article))
			}
		);
	}
	
	/// prepare AJAX call in modal box: edit extra on pagezone_page or article
	function extra_edit(table, uid, pz_uid, is_concrete_article) {
/// \todo: add be_mode            
			subModalExtraEdit(table, uid, pz_uid, is_concrete_article);
	}

	/// prepare AJAX call in modal box: insert extra on pagezone_page or article
	function extra_insert_after(origin_uid, pz_uid, paragraph, new_at_top, is_concrete_article) {
/// \todo: add be_mode
		subModalExtraInsertAfter(origin_uid, pz_uid, paragraph, new_at_top, is_concrete_article);
	}	
	
	
	/// store data in field (if field is changed a undo/store option is added to field; one field editable at a time)
	function extra_save_field(pz_uid, extra_uid, value, type, is_concrete_article) {
		switch(type) {
			case 'para':
				document.enter_para_uid = null;
			break;
			case 'notes':
				document.enter_notes_uid = null;
			break;
		}
		var request = new top.Ajax.Request(
				top.path + "typo3conf/ext/newspaper/mod3/index.php",
				{
				method: 'get',
				parameters: "extra_save_field=1&pz_uid=" + pz_uid + "&extra_uid=" + extra_uid + "&value=" + value + "&type=" + type + "&no_cache=" + new Date().getTime(),
				onCreate: eval(get_onCreate_function(is_concrete_article)),
				onSuccess: eval(get_onSuccess_function(is_concrete_article))
			}
		);
	}
	
	

	

/// functions for concrete articles only /////////////////////////////////////////
	
	
	
	
	
	
	
	
/// modal box functions
	
	// new at top = show input field for paragraph
	function subModalExtraInsertAfter(origin_uid, pz_uid, paragraph, new_at_top, is_concrete_article) {
		var width = Math.min(700, top.getViewportWidth() - 100); 
		var height = top.getViewportHeight() - 50;
		var closehtml = (is_concrete_article)? escape(t3BackendObject.path + "typo3conf/ext/newspaper/mod3/res/close_reload_in_concrete_article.html?pz_uid=" + pz_uid) : t3BackendObject.path + "typo3conf/ext/newspaper/mod3/res/close.html"; 
		top.showPopWin(
			t3BackendObject.path + "typo3conf/ext/newspaper/mod3/index.php?chose_extra=1&origin_uid=" + origin_uid + "&pz_uid=" + pz_uid + "&paragraph=" + paragraph + "&new_at_top=" + new_at_top + "&is_concrete_article=" + is_concrete_article + "&returnUrl=" + closehtml,
			width, 
			height, 
			null, 
			true
		);
	}
	
	function subModalExtraEdit(table, uid, pz_uid, is_concrete_article) {
		var width = Math.min(700, top.getViewportWidth() - 100); 
		var height = top.getViewportHeight() - 50;
		var closehtml = (is_concrete_article)? escape(t3BackendObject.path + "typo3conf/ext/newspaper/mod3/res/close_reload_in_concrete_article.html?pz_uid=" + pz_uid) : t3BackendObject.path + "typo3conf/ext/newspaper/mod3/res/close.html";
		top.showPopWin(
			t3BackendObject.path + "typo3/alt_doc.php?returnUrl=" + closehtml + "&edit[" + table + "][" + uid + "]=edit",
			width, 
			height, 
			null, 
			true
		);
	}

	function extra_insert_after_dummy(origin_uid, pagezone_uid) {
/// \todo: remove after testing
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{
				method: 'get',
				parameters: "extra_insert_after_dummy=1&origin_uid=" + origin_uid + "&pz_uid=" + pagezone_uid + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			}
		);
	}


	function extra_set_show(extra_uid, show) {
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{
				method: 'get',
				parameters: "extra_set_show=1&extra_uid=" + extra_uid + "&show=" + show + "&no_cache=" + new Date().getTime()
			}
		);
	}

	function extra_set_pass_down(pz_uid, extra_uid, pass_down) {
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{
				method: 'get',
				parameters: "extra_set_pass_down=1&pz_uid=" + pz_uid + "&extra_uid=" + extra_uid + "&pass_down=" + pass_down + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			}
		);
	}

	function page_type_change(pt_uid) {
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{
				method: 'get',
				parameters: "extra_page_type_change=1&pt_uid=" + pt_uid + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			}
		);
	}

	function pagezone_type_change(pzt_uid) {
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{
				method: 'get',
				parameters: "extra_pagezone_type_change=1&pzt_uid=" + pzt_uid + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			}
		);
	}

	
	function inheritancesource_change(pz_uid, value) {
	var request = new top.Ajax.Request(
			top.path + "typo3conf/ext/newspaper/mod3/index.php",
			{
			method: 'get',
			parameters: "inheritancesource_change=1&pz_uid=" + pz_uid + "&value=" + value + "&no_cache=" + new Date().getTime(),
			onCreate: processing,
			onSuccess: reload
		}
	);
}

	
	

/// \to do: remove after all calls are switched to page/article version ????
	function processing() {

		img = document.createElement('img');
		img_src = document.createAttribute('src');
		img_src.nodeValue = top.path + 'typo3/gfx/spinner.gif';
		img.setAttributeNode(img_src);

		layer_style = document.createAttribute('style');
		layer_style.nodeValue = 'color: red; position: absolute; z-index: 99; text-align: center; background: rgba(208, 208, 208, 0.8) none repeat scroll 0 0; top: 0; bottom: 0; left: 0; right: 0; padding-top: 33%;';

		layer = document.createElement('div');
		layer.setAttributeNode(layer_style);

		layer.appendChild(img);
		self.document.getElementsByTagName('body')[0].appendChild(layer);
	}
	
	// direct reload (no popup/modalbox involved ...)
	function reload(data) {
		if (data.responseText == '') {
			// empty string, so no error message
/// \todo: currently no_cache param are ADDED, must be REPLACED
			self.location.href = self.location.href + "&no_cache=" + new Date().getTime();
		} else {
			// display error message
			self.document.getElementsByTagName('body')[0].innerHTML = data.responseText;
		}
	}

	
	
	
	
	
	
	
/// handling paragraphs and notes in pagezone_page and article	
	
	document.enter_para_uid = null; // if set to false, a paragraph might be changed
	document.def_para = new Array(); // stores the current value for all paragraphs being displayed
	document.enter_notes_uid = null; // if set to false, a note might be changed
	document.def_notes = new Array(); // stores the current value for all notes being displayed
	
	// note: if paragraph AND notes are filed in the same form, data is lost if both types contains unsaved data
	function enterField(extra_uid, type) {
		old_type_uid = eval("document.enter_" + type + '_uid');
//top.console.log('enterField     e uid' + extra_uid + ', type: ' + type + ', old: ' + old_type_uid);
		if (old_type_uid != null) {
			// undo unsaved change
			undoField(old_type_uid, type);
		}

		// \todo: type check ...
		
		document.getElementById(type + '_td_' + extra_uid).style.backgroundColor = 'red';
		document.getElementById('save_' + type + '_' + extra_uid).style.display = 'inline';
		
		switch(type) {
			case 'para':
				document.enter_para_uid = extra_uid;
			break;
			case 'notes':
				document.enter_notes_uid = extra_uid;
			break;
		}
		
	}

	function undoField(extra_uid, type) {
		
		if (type == null) return false;
		
//top.console.log('undo   type: ' + type + 'e uid: ' + extra_uid);
		document.getElementById(type + '_td_' + extra_uid).style.backgroundColor = '';
		document.getElementById('save_' + type + '_' + extra_uid).style.display = 'none';

		document.getElementById(type + '_' + extra_uid).value = eval("document.def_" + type + "[extra_uid]");

		switch(type) {
			case 'para':
				document.enter_para_uid = null;
			break;
			case 'notes':
				document.enter_notes_uid = null;
			break;
		}
		
	}

	function saveField(pz_uid, extra_uid, type, is_concrete_article) {
		value = document.getElementById(type + '_' + extra_uid).value;
//top.console.log("save " + value + ', e uid: ' + extra_uid);
		extra_save_field(pz_uid, extra_uid, value, type, is_concrete_article);
		
		// store this value as new default (not needed if page is reloaded ...)
/*
		switch(type) {
			case 'para':
				document.def_para[extra_uid] = value;
			break;
			case 'notes':
				document.def_notes[extra_uid] = value;
			break;
		}
*/		
	}



/// template set dropdown handling: ATTENTION: copy of this function in res/be/pagetype_pagezonetype_4section.js
	function storeTemplateSet(table, uid, value) {
		uid = parseInt(uid);
		var request = new top.Ajax.Request(
			top.path + "typo3conf/ext/newspaper/mod3/index.php",
			{
				method: 'get',
				parameters: "templateset_dropdown_store=1&table=" + table + "&uid=" + uid + "&value=" + value + "&no_cache=" + new Date().getTime()
			}
		);
	}

//////////////////// Functions for tabs

var tabManagement =  {
    tabIds: [],
    activeTabClass: 'extra_tab_act',

    initialize: function() {        
        tabManagement.confirmMessage = confirmationMessage;
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
            var returnurl =  t3BackendObject.path + 'typo3conf/ext/newspaper/mod3/res/closeTab.html';
            $(tab_id).innerHTML='<iframe height="840px" width="100%" id="iframe_'+id+'" src="alt_doc.php?returnUrl='+returnurl+'&edit['+tableName+']['+id+']=edit""></iframe>';

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
                var saveDokInput = new Element('input', {type: 'hidden', name: '_savedok' + suffix, value: 1});
                iframeDok.forms[0].appendChild(saveDokInput);
            });
            $A(iframeDok.getElementsByName('doSave')).each(function(elem) { elem.value = 1 })
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
        return false;
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

    askUserContinueIfDirty: function() {
        var allowSubmit = true;
        if(tabManagement.isDirty()) {
            allowSubmit = confirm(tabManagement.confirmMessage);
        }
        return allowSubmit;
    },

    removeTab: function(extra_class, extra_uid) {
        tabManagement.tabIds = tabManagement.tabIds.without(extra_class + '_' + extra_uid);
    },

    clearTabCache: function() {
        tabManagement.tabIds = [];
    }

};


//////////// copied from mod3_new_extra some slightly modified for usage with tabs others just because of scope
function getChosenExtra() {
    if (document.getElementById('extra_list').selectedIndex < 0) {
        alert(msgNoExtraSelected);
        return false;
    }
    return document.getElementById('extra_list').value;
}

function getParagraph() {
    return parseInt(document.getElementById('paragraph').value);
}

function extra_insert_after_NEW(origin_uid, pz_uid, article_uid, in_article, paragraphUsed, show) {
    var target_uid = article_uid > 0? article_uid : pz_uid;
    var closehtml = (in_article)? escape("close_reload_in_concrete_article.html?pz_uid=" + target_uid) : "close.html";
//		var new_extra_paragraph_position_data = '';
//		 if(paragraphUsed) {
//		    new_extra_paragraph_position_data = '&paragraph=' + getParagraph();
//		 }
    var extra_class_sysfolder = getChosenExtra();
    var extra_class = top.splitAtPipe(extra_class_sysfolder, 0);
    var extra_sysfolder = top.splitAtPipe(extra_class_sysfolder, 1);
    if (!extra_class || !extra_sysfolder) {
        alert('Fatal error: Value in list of extras has wrong structure! Please contact developers!');
        return false;
    }
    extra_sysfolder = parseInt(extra_sysfolder);
    if (extra_class != false) { //extra is related to pagezone in save hook!
        var loc = top.path + "typo3conf/ext/newspaper/mod3/index.php";
        new Ajax.Request(loc, {
            parameters: {'extra_create' : 1, 'article_uid' : article_uid, 'extra_class' : extra_class, 'origin_uid' : origin_uid, 'pz_uid' : pz_uid, 'paragraph' : getParagraph(), 'doShow' : show},
            onCreate: processing_in_article,
            onSuccess: function(transport) {
                if(transport) {
                    var  data = transport.responseJSON;
                    $('extras').innerHTML = data.content;
                    tabManagement.clearTabCache();
                    tabManagement.show(extra_class+'_'+data.extra_uid);
                }
            }
        });
    }
    return false;
}

function extra_insert_after_POOL(origin_uid, pz_uid, in_article, paragraphUsed) {
    if (in_article) alert("pool: in article not yet implemented");
        new_extra_paragraph_position_data = '';
    if(paragraphUsed) {
        new_extra_paragraph_position_data = '&paragraph=' + getParagraph();
    }
    extra_class_sysfolder = getChosenExtra();
    extra_class = top.splitAtPipe(extra_class_sysfolder, 0);
    if (extra_class == false) {
        alert('Fatal error: Value in list of extras has wrong structure! Please contact developers!');
        return false;
    }
    if (extra_class != false) {
//self.location.href = "index.php?chose_extra_from_pool=1&origin_uid=" + origin_uid + "&extra=" + extra_class + "&pool_extra_pz_uid=" + pz_uid + "&pool_extra_after_origin_uid=" + origin_uid + new_extra_paragraph_position_data
        self.location.href = "index.php?chose_extra_from_pool=1&origin_uid=" + origin_uid + "&extra=" + extra_class + "&pz_uid=" + pz_uid + new_extra_paragraph_position_data;
    }
}

/**
 * Intercepts original function if there are unsaved iframes and warns user
 * @param func
 */
var interceptIfDirty = function(func) {
    //parameter orginalFunc is passed from wrap function itself
    return func.wrap(function(orginalFunc) {
                if(tabManagement.isDirty()) {
                    tabManagement.submitTabs();
                    sleep(500); //todo: hack to prevent race condition
                }
                var args = Array.prototype.slice.call(arguments, 1);
                return orginalFunc.apply(this, args);
            });
}

function sleep(milliseconds) {
  var start = new Date().getTime();
  for (var i = 0; i < 1e7; i++) {
    if ((new Date().getTime() - start) > milliseconds){
      break;
    }
  }
}


/**
 * Stuff that should be executed after the dom is loaded
 *
 */
document.observe('dom:loaded', function() {
    tabManagement.initialize();
    tabManagement.show($('lastTab').value);

    //handling this inside a loop did not work
    extra_insert_after = interceptIfDirty(extra_insert_after);
    extra_insert_after_NEW = interceptIfDirty(extra_insert_after_NEW);
    extra_insert_after_POOL = interceptIfDirty(extra_insert_after_POOL);
    extra_move_after = interceptIfDirty(extra_move_after);
    extra_delete = interceptIfDirty(extra_delete);
    extra_shortcut_create = interceptIfDirty(extra_shortcut_create);


});


//{/literal}	