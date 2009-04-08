var path = window.location.pathname;
path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"



	function listPages(section_id) {
		/// ajax call: list page types 
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "list_page_types&param=[section]" + section_id + "&no_cache=" + new Date().getTime(),
					onSuccess: updatePageTypePageZoneType
				}
		);
		document.getElementById('pagetype_pagezone').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	}




	function activatePageType(section_id, pagetype_id, message) {
		
		if (message == undefined) {
			alert('Illegal function call');
			return;
		}
		
		// user must confirm the he knows what he's doing
		if (!confirm(message)) return; 
		
		/// ajax call: activate page type 
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "activate_page_type&param=[section]" + section_id + "|[pagetype]" + pagetype_id + "&no_cache=" + new Date().getTime(),
					onSuccess: updatePageTypePageZoneType
				}
		);
		document.getElementById('pagetype_pagezone').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	}
		
		
	function editActivePage(section_id, page_id) {
		/// ajax call: edit active page (zones) 
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "edit_page_type&param=[section]" + section_id + "|[page]" + page_id + "&no_cache=" + new Date().getTime(),
					onSuccess: updatePageTypePageZoneType
				}
		);
		document.getElementById('pagetype_pagezone').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	}

	
	function deletePage(section_id, pagetype_id, message) {
		
		if (message == undefined) {
			alert('Illegal function call');
			return;
		}
		
		// user must confirm the he knows what he's doing
		if (!confirm(message)) return; 
		
		/// ajax call: activate page type 
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "delete_page&param=[section]" + section_id + "|[pagetype]" + pagetype_id + "&no_cache=" + new Date().getTime(),
					onSuccess: updatePageTypePageZoneType
				}
		);
		document.getElementById('pagetype_pagezone').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	}
	

	function activatePageZoneType(section_id, page_id, pagezone_type, message) {
		
		if (message == undefined) {
			alert('Illegal function call');
			return;
		}
		
		// user must confirm the he knows what he's doing
		if (!confirm(message)) return; 
		
		/// ajax call: edit active page (zones) 
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "activate_pagezone_type&param=[section]" + section_id + "|[page]" + page_id + "|[pagezonetype]" + pagezone_type + "&no_cache=" + new Date().getTime(),
					onSuccess: updatePageTypePageZoneType
				}
		);
		document.getElementById('pagetype_pagezone').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	}
	
	
	function deletePageZone(section_id, page_id, pagezone_type, message) {
		
		if (message == undefined) {
			alert('Illegal function call');
			return;
		}
		
		// user must confirm the he knows what he's doing
		if (!confirm(message)) return; 
		
		/// ajax call: edit active page (zones) 
		var request = new Ajax.Request(
			path + "typo3conf/ext/newspaper/mod1/index.php",
				{
					method: 'get',
					parameters: "delete_pagezone&param=[section]" + section_id + "|[page]" + page_id + "|[pagezonetype]" + pagezone_type + "&no_cache=" + new Date().getTime(),
					onSuccess: updatePageTypePageZoneType
				}
		);
		document.getElementById('pagetype_pagezone').innerHTML = '<img src="' + path + 'typo3/gfx/spinner.gif"/>';
	}



	
	
	
	
	
	/// one update function for all ajax calls
	function updatePageTypePageZoneType(request) {
		var json = request.responseText.evalJSON(true);
		//TODO: working in ff, what about the other browsers???
		document.getElementById('pagetype_pagezone').innerHTML = json.html;
	}
