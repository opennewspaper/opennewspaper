var path = window.location.pathname;
path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"


	function activatePageType(section_id, pagetype_id, message) {
/*
		if (message == undefined) {
			alert('Illegal function call');
			return;
		}
		// user must confirm that he knows what he's doing
		if (!confirm(message)) return; 
*/

		/// ajax call: activate page type 
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "activate_page_type&param=[section]" + section_id + "|[pagetype]" + pagetype_id + "&no_cache=" + new Date().getTime(),
					onSuccess: updatePageTypePageZoneType
				}
		);
		document.getElementById('pagetype_pagezonetype').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	}
		

	function deletePage(section_id, page_id, message) {
		
		if (message == undefined) {
			alert('Illegal function call');
			return;
		}
		
		// user must confirm that he knows what he's doing
		if (!confirm(message)) return; 
		
		/// ajax call: delete page 
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "delete_page&param=[section]" + section_id + "|[page]" + page_id + "&no_cache=" + new Date().getTime(),
					onSuccess: updatePageTypePageZoneType
				}
		);
		document.getElementById('pagetype_pagezonetype').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	}
	

	function activatePageZoneType(section_id, page_id, pagezone_type, message) {
/*		
		if (message == undefined) {
			alert('Illegal function call');
			return;
		}
		// user must confirm that he knows what he's doing
		if (!confirm(message)) return; 
*/

		/// ajax call: active page zone type 
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "activate_pagezone_type&param=[section]" + section_id + "|[page]" + page_id + "|[pagezonetype]" + pagezone_type + "&no_cache=" + new Date().getTime(),
					onSuccess: updatePageTypePageZoneType
				}
		);
		document.getElementById('pagetype_pagezonetype').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	}
	
	
	function deletePageZone(section_id, page_id, pagezone_id, message) {
		
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
					onSuccess: updatePageTypePageZoneType
				}
		);
		document.getElementById('pagetype_pagezonetype').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	}

	
	
	
	
	/// one update function for all ajax calls
	function updatePageTypePageZoneType(request) {
		var json = request.responseText.evalJSON(true);
		document.getElementById('pagetype_pagezonetype').innerHTML = json.html;
	}
