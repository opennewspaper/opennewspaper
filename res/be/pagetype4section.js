var path = window.location.pathname;
path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"


	function activatePageType(section_id, pagetype_id, message) {
		
		if (message == undefined) {
			alert('Illegal function call');
			return;
		}
		
		// user must confirm the he knows what he's doing
		if (!confirm(message)) return; 
		
		// ajax call: activate page type 
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "activate_page_type&param=" + section_id + "|" + pagetype_id + "&no_cache=" + new Date().getTime(),
					onSuccess: updateActivatePageType
				}
		);
	}
	function updateActivatePageType(request) {
		var json = request.responseText.evalJSON(true);
		//TODO: working in ff, what about the other browsers???
		document.getElementById('pagetype_pagezone').innerHTML = json.html;
	}
	
		
		
		
		
	function editActivePage(page_id) {
		// ajax call: edit active page (zones) 
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "edit_page_type&param=" + page_id + "&no_cache=" + new Date().getTime(),
					onSuccess: updateEditPageType
				}
		);
		document.getElementById('pagetype_pagezone').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	}
	function updateEditPageType(request) {
		var json = request.responseText.evalJSON(true);
		//TODO: working in ff, what about the other browsers???
		document.getElementById('pagetype_pagezone').innerHTML = json.html;
	}
	
