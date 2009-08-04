<script language="javascript" >
	document.change_para = false; // if set to false, a paragraph might be changed
	document.def_para = new Array(); // stores the current value for all paragraphs being displayed
	document.change_notes = false; // if set to false, a note might be changed
	document.def_notes = new Array(); // stores the current value for all notes being displayed

	
	
	function changeField(extra_uid, type) {ldelim}
		if (eval("document.change_" + type)) {ldelim}
			alert('todo: message ... erst speichern ... ' + extra_uid + ',' + eval("document.def_" + type + "[extra_uid]"));
			document.getElementById(type + '_' + extra_uid).value = eval("document.def_" + type + "[extra_uid]"); // undo
			return false;
		{rdelim}

		// \todo: type check ...
		
		document.getElementById(type + '_td_' + extra_uid).style.backgroundColor = 'red';
		document.getElementById('save_' + type + '_' + extra_uid).style.display = 'inline';
		
		switch(type) {ldelim}
			case 'para':
				document.change_para = true;
			break;
			case 'notes':
				document.change_notes = true;
			break;
		{rdelim}
		
	{rdelim}

	function undoField(extra_uid, type) {ldelim}
		document.getElementById(type + '_td_' + extra_uid).style.backgroundColor = '';
		document.getElementById('save_' + type + '_' + extra_uid).style.display = 'none';

		document.getElementById(type + '_' + extra_uid).value = eval("document.def_" + type + "[extra_uid]");

		switch(type) {ldelim}
			case 'para':
				document.change_para = false;
			break;
			case 'notes':
				document.change_notes = false;
			break;
		{rdelim}
		
	{rdelim}

	function saveField(pz_uid, extra_uid, type) {ldelim}
		value = document.getElementById(type + '_' + extra_uid).value;
		extra_save_field(pz_uid, extra_uid, value, type);
		
		// store this value as new default (not needed if page is reloaded ...)
		switch(type) {ldelim}
			case 'para':
				document.def_para[extra_uid] = value;
			break;
			case 'notes':
				document.def_notes[extra_uid] = value;
			break;
		{rdelim}
		
	{rdelim}


</script>