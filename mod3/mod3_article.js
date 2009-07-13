<script language="javascript" >
	document.change_para = false; // if set to false, a paragraph might be changed
	document.def = new Array();

	function changeParagraph(extra_uid) {ldelim}
		if (document.change_para) {ldelim}
			alert('todo: message ... erst speichern ... ' + extra_uid + ',' + document.def[extra_uid]);
			document.getElementById('para_' + extra_uid).value = document.def[extra_uid]; // undo
			return false;
		{rdelim}

		// \todo: integer check ...
		
		document.getElementById('para_td_' + extra_uid).style.backgroundColor = 'red';
		document.getElementById('save_' + extra_uid).style.display = 'inline';

		document.change_para = true;
		
	{rdelim}

	function undoParagraph(extra_uid) {ldelim}
		document.getElementById('para_td_' + extra_uid).style.backgroundColor = '';
		document.getElementById('save_' + extra_uid).style.display = 'none';

		document.getElementById('para_' + extra_uid).value = document.def[extra_uid];

		document.change_para = false;
		
	{rdelim}

	function saveParagraph(extra_uid) {ldelim}
		value = document.getElementById('para_' + extra_uid).value;
		extra_save_para(extra_uid, value);
		document.def[extra_uid] = value; // store this value as new default (not needed if page is reloaded ...)
	{rdelim}


</script>