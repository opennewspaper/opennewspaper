{literal}
<script language="javascript">

// this script assumes prototype (ajax) to be available







// add javascript and css if not available (backend called in popup WITHOUT typo3 frameset where these scripts are loaded to)
var t3BackendObject = getTypo3BackendObject();
if (t3BackendObject != top) {
	loadJsCssFile(t3BackendObject.path + "typo3conf/ext/newspaper/contrib/subModal/common.js", "js");
	loadJsCssFile(t3BackendObject.path + "typo3conf/ext/newspaper/contrib/subModal/subModal.js", "js");
	loadJsCssFile(t3BackendObject.path + "typo3conf/ext/newspaper/res/be/extra/util.js", "js");
	loadJsCssFile(t3BackendObject.path + "typo3conf/ext/newspaper/res/be/util.js", "js");
	loadJsCssFile(t3BackendObject.path + "typo3conf/ext/newspaper/contrib/subModal/subModal.css", "css");
}

//http://www.javascriptkit.com/javatutors/loadjavascriptcss.shtml
function loadJsCssFile(filename, filetype) {
	if (filetype == "js") { //if filename is a external JavaScript file
		var fileref = document.createElement('script');
		fileref.setAttribute("type", "text/javascript");
		fileref.setAttribute("src", filename);
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
	}
	
	/// the list of extras has to be reloaded from the server (needed for modal box or saveField ajax calls)
	function reload_in_article(pz_uid) {
		if (t3BackendObject == top) {
			ref = top.content.list_frame
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
	
	

	
	
/// functions for placement pagezone_page //////////////////////////////////////
	
	
	
	
	
/// functions for placement (default) article //////////////////////////////////	
		
	/// AJAX call: create extra on article, started by shortcut link
	function extra_shortcut_create(article_uid, extra_class, extra_uid) {
		var request = new top.Ajax.Request(
			top.path + "typo3conf/ext/newspaper/mod3/index.php",
			{
				method: 'get',
				parameters: "extra_shortcut_create=1&article_uid=" + article_uid + "&extra_class=" + extra_class + "&extra_uid=" + extra_uid + "&no_cache=" + new Date().getTime(),
				onCreate: eval(get_onCreate_function(1)),
				onSuccess: eval(get_onSuccess_function(1))
			}
		);
	}
		
		
		

	
/// functions for placement article AND concrete article ///////////////////////
	
	/// AJAX call: delete extra on pagezone_page or article
	function extra_delete(pz_uid, extra_uid, message, is_concrete_article) {
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
		var closehtml = (is_concrete_article)? escape(t3BackendObject.path + "typo3conf/ext/newspaper/mod3/close_reload_in_concrete_article.html?pz_uid=" + pz_uid) : t3BackendObject.path + "typo3conf/ext/newspaper/mod3/close.html"; 
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
		var closehtml = (is_concrete_article)? escape(t3BackendObject.path + "typo3conf/ext/newspaper/mod3/close_reload_in_concrete_article.html?pz_uid=" + pz_uid) : t3BackendObject.path + "typo3conf/ext/newspaper/mod3/close.html";
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
				parameters: "extra_set_show=1&extra_uid=" + extra_uid + "&show=" + show + "&no_cache=" + new Date().getTime(),
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

	

	
</script>
{/literal}