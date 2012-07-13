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
} else {
	// load some scriptaculous stuff (for popups)
	loadJsCssFile(t3BackendObject.path + "typo3/contrib/scriptaculous/scriptaculous.js", "js", "load=builder,effects,controls");
    loadJsCssFile(t3BackendObject.path + "typo3conf/ext/newspaper/res/be/autocomplete.css", "css");
	// subModal is not supported for articles ... \todo: FIXME
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

	// returns object containing NpTools.getViewportWidth() function (or returns false if not available)
	function getTypo3BackendObject() {
		if (typeof top.NpTools != 'undefined' && typeof top.NpTools.getViewportWidth == 'function') {
			return top; // called in "normal" typo3 backend
		}
		if (opener != null && typeof opener.top.NpTools.getViewportWidth == 'function') {
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
        top.NpBackend.showProgress();
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
	function extra_shortcut_create(article_uid, extra_class, extra_uid, paragraph, show) {
		var request = new top.Ajax.Request(
			top.path + "typo3conf/ext/newspaper/mod3/index.php",
			{
				method: 'get',
				parameters: "extra_shortcut_create=1&article_uid=" + article_uid + "&extra_class=" + extra_class + "&extra_uid=" + extra_uid + "&paragraph=" + paragraph + "&doShow=" + show +"&no_cache=" + new Date().getTime(),
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
		var width = Math.min(700, top.NpTools.getViewportWidth() - 100);
		var height = top.NpTools.getViewportHeight() - 50;
		var closehtml = (is_concrete_article)? escape(t3BackendObject.path + "typo3conf/ext/newspaper/mod3/res/close_reload_in_concrete_article.html?pz_uid=" + pz_uid) : t3BackendObject.path + "typo3conf/ext/newspaper/mod3/res/close.html";
		top.showPopWin(
			t3BackendObject.path + "typo3conf/ext/newspaper/mod3/index.php?chose_extra=1&origin_uid=" + origin_uid + "&pz_uid=" + pz_uid + "&paragraph=" + paragraph + "&new_at_top=" + new_at_top + "&is_concrete_article=" + is_concrete_article + "&returnUrl=" + closehtml,
			width,
			height,
			null,
			false // no submodal close button
		);
	}

	function subModalExtraEdit(table, uid, pz_uid, is_concrete_article) {
		var width = Math.min(700, top.NpTools.getViewportWidth() - 100);
		var height = top.NpTools.getViewportHeight() - 50;
		var closehtml = (is_concrete_article)? escape(t3BackendObject.path + "typo3conf/ext/newspaper/mod3/res/close_reload_in_concrete_article.html?pz_uid=" + pz_uid) : t3BackendObject.path + "typo3conf/ext/newspaper/mod3/res/close.html";
		top.showPopWin(
			t3BackendObject.path + "typo3/alt_doc.php?returnUrl=" + closehtml + "&edit[" + table + "][" + uid + "]=edit",
			width,
			height,
			null,
			false // no submodal close button
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


/**
 * Toggle value "show" of given extra
 * Checkbox needs to have id "show_extra_" plus extra_uid
 * @param extra_uid Extra uid
 */
	function extra_set_show(extra_uid) {
        var checkboxId = "show_extra_" + parseInt(extra_uid);
        var show;
        show = (document.getElementById(checkboxId).checked)? 1 : 0;
        var request = new top.Ajax.Request(
            top.path + "typo3conf/ext/newspaper/mod3/index.php",
            {
                method: 'get',
                parameters: "extra_set_show=1&extra_uid=" + extra_uid + "&show=" + show + "&no_cache=" + new Date().getTime(),
                onCreate: processing,
                onSuccess: hideProcessing
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

// clipboard functions

	function extra_cut(pagezone_uid, extra_uid) {
		extra_cut_copy(pagezone_uid, extra_uid, 'cutClipboard');
	}
	function extra_copy(pagezone_uid, extra_uid) {
		extra_cut_copy(pagezone_uid, extra_uid, 'copyClipboard');
	}
	function extra_cut_copy(pagezone_uid, extra_uid, type) {
		var request = new top.Ajax.Request(
			top.path + "typo3conf/ext/newspaper/mod3/index.php",
			{
				method: 'get',
				parameters: "tx_newspaper_mod3[ajaxController]=" + type + "&tx_newspaper_mod3[e_uid]=" + extra_uid + "&tx_newspaper_mod3[pz_uid]=" + pagezone_uid + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			}
		);
	}

	function extra_paste(origin_uid, pagezone_uid, message) {
		if (!confirm(message)) {
			return; // user must confirm that he knows what he's doing
		}
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{
				method: 'get',
				parameters: "tx_newspaper_mod3[ajaxController]=pasteClipboard&tx_newspaper_mod3[origin_uid]=" + origin_uid + "&tx_newspaper_mod3[pz_uid]=" + pagezone_uid + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			}
		);
	}

	function clear_clipboard() {
		var request = new top.Ajax.Request(
			top.path + "typo3conf/ext/newspaper/mod3/index.php",
			{
				method: 'get',
				parameters: "tx_newspaper_mod3[ajaxController]=clearClipboard" + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			}
		);
	}


/**
 * Show spinner
 */
/// \to do: remove after all calls are switched to page/article version ????
	function processing() {
        top.NpBackend.showProgress();
	}


/**
 * Hide spinner
 */
    function hideProcessing() {
        top.NpBackend.hideProgress();
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
		var value = document.getElementById(type + '_' + extra_uid).value;
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
//        tabManagement.confirmMessage = confirmationMessage;
        tabManagement._hideAllTabs();
    },

    _getTablenameAndId: function(tabId) {
        var tmp = tabId.split('_');
        var tableId = tmp.pop();
        var tableName = tmp.join('_');
        return {'table': tableName, 'id' : tableId};
    },

    show: function(tabId, params) {
        var tableAndId = tabManagement._getTablenameAndId(tabId);
        var tableName = tableAndId.table;
        var id = tableAndId.id;

        var isExtraTab = !isNaN(id); //last part of string is concrete id when its an extra tab
        var tab_id = isExtraTab ? tableName + '_' + id : tabId; //if it's no extra the passed in tabid was a real div#id

        tabManagement._hideAllTabs();

        //after an ajax reload the iframe must be loaded again but tabIds already contains the current tab_id
        //therefore check for empty div.
        // isExtraTab is true when the current tab is an extra and therefore the iframe must be loaded.
        if( ($(tab_id).innerHTML == "") && isExtraTab) {

            var closehtml = t3BackendObject.path + "typo3conf/ext/newspaper/mod3/res/closeTab.html";
//            $(tab_id).innerHTML='<iframe height="840px" width="100%" id="iframe_'+id+'" src="alt_doc.php?edit['+tableName+']['+id+']=edit&returnUrl='+closehtml+'"></iframe>';
            $(tab_id).innerHTML='<iframe style="height:840px;" width="100%" id="iframe_'+id+'" src="alt_doc.php?edit['+tableName+']['+id+']=edit&returnUrl='+closehtml+'"></iframe>';

            //after reload the tab_id is already inside the list
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
     * The originalFunction is an array consisting of the function itself, its arguments and context
     * @param saveInput savedok or saveandclosedok
     * @param orginalFunction function that should be executed after all tabs have been saved
     */
    submitTabs: function(saveInput, orginalFunction) {

        tabManagement.submitNext = function() {
            //while there are tabs submit them
            if(tabManagement.tabIds.size() > 0) {
                var tableAndId = tabManagement._getTablenameAndId(tabManagement.tabIds.pop());
                var frameName = 'iframe_'+ tableAndId.id;
                if(!$(frameName)) {
                    alert('Extra konnte nicht gespeichert werden: ' + frameName);
                    return false;
                }
                var iframeDok = $(frameName).contentDocument;
                if(iframeDok == null) {
                    alert("No document for " + frameName + " found");
                    return false;
                }

                tabManagement.addSaveInput(iframeDok, '_saveandclosedok');
                iframeDok.forms[0].submit();

            //no tabs anymore, save article
            } else {
                if(saveInput) { //saveInput is set when one of the article savebuttons is pressed.
                    tabManagement.addSaveInput(document, saveInput.name);
                    document.forms[0].submit();
                } else {
                    //submit tabs was not called via standard save button, but function on overview tab.
                    //in that case the called function (like change paragraph) is executed here, after all tabs have been saved
                    return orginalFunction[0].apply(orginalFunction[2], orginalFunction[1]);
                }
            }
        }

        tabManagement.submitNext();
        return false; //it is very important to don't submit
    },

    addSaveInput : function(documentObject, savetype) {
        ['.x', '.y'].each(function(suffix) {
            var saveDokInput = new Element('input', {type: 'hidden', name: savetype + suffix, value: 1});
            if(!documentObject.forms[0]) {
                alert('fehler beim Speicher der Webelemente.');
                return false;
            }
            documentObject.forms[0].appendChild(saveDokInput);
        });
        $A(documentObject.getElementsByName('doSave')).each(function(elem) { elem.value = 1 });
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
    var extra_class_sysfolder;
    if (extra_class_sysfolder = getChosenExtra()) {
	    var extra_class = top.NpTools.splitAtPipe(extra_class_sysfolder, 0);
	    var extra_sysfolder = top.NpTools.splitAtPipe(extra_class_sysfolder, 1);
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
    }
    return false;
}

function extra_insert_after_POOL(origin_uid, pz_uid, in_article, paragraphUsed) {
    if (in_article) alert("pool: in article not yet implemented");
        new_extra_paragraph_position_data = '';
    if(paragraphUsed) {
        new_extra_paragraph_position_data = '&paragraph=' + getParagraph();
    }
    if (extra_class_sysfolder = getChosenExtra()) {
        extra_class = top.NpTools.splitAtPipe(extra_class_sysfolder, 0);
        if (extra_class != false) {
//self.location.href = "index.php?chose_extra_from_pool=1&origin_uid=" + origin_uid + "&extra=" + extra_class + "&pool_extra_pz_uid=" + pz_uid + "&pool_extra_after_origin_uid=" + origin_uid + new_extra_paragraph_position_data
            self.location.href = "index.php?chose_extra_from_pool=1&origin_uid=" + origin_uid + "&extra=" + extra_class + "&pz_uid=" + pz_uid + new_extra_paragraph_position_data;
        }
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
                    var args = Array.prototype.slice.call(arguments, 1);
                    tabManagement.submitTabs(null, [orginalFunc, args, this]);
                } else {
                    var args = Array.prototype.slice.call(arguments, 1);
                    return orginalFunc.apply(this, args);
                }
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
    saveField = interceptIfDirty(saveField);


});


//{/literal}