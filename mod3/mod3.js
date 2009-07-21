<script language="javascript">

	function extra_save_para(pz_uid, extra_uid, value) {ldelim}
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{ldelim}
				method: 'get',
				parameters: "extra_save_para=1&pz_uid=" + pz_uid + "&extra_uid=" + extra_uid + "&value=" + value + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			{rdelim}
		);
	{rdelim}

	
	function toggle_show_levels_above(checked) {ldelim}
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{ldelim}
				method: 'get',
				parameters: "toggle_show_levels_above=1&checked=" + checked + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			{rdelim}
		);
	{rdelim}


	function extra_insert_after_dummy(origin_uid, pagezone_uid) {ldelim}
/// \todo: remove after testing
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{ldelim}
				method: 'get',
				parameters: "extra_insert_after_dummy=1&origin_uid=" + origin_uid + "&pz_uid=" + pagezone_uid + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			{rdelim}
		);
	{rdelim}


	function extra_move_after(origin_uid, pz_uid, extra_uid) {ldelim}
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{ldelim}
				method: 'get',
				parameters: "extra_move_after=1&origin_uid=" + origin_uid + "&pz_uid=" + pz_uid + "&extra_uid=" + extra_uid + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			{rdelim}
		);
	{rdelim}

	function extra_delete(pz_uid, extra_uid, message) {ldelim}
		// user must confirm that he knows what he's doing
		if (!confirm(message)) return;
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{ldelim}
				method: 'get',
				parameters: "extra_delete=1&pz_uid=" + pz_uid + "&extra_uid=" + extra_uid + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			{rdelim}
		);
	{rdelim}
	
	function extra_set_show(extra_uid, show) {ldelim}
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{ldelim}
				method: 'get',
				parameters: "extra_set_show=1&extra_uid=" + extra_uid + "&show=" + show + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			{rdelim}
		);
	{rdelim}

	function extra_set_pass_down(pz_uid, extra_uid, pass_down) {ldelim}
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{ldelim}
				method: 'get',
				parameters: "extra_set_pass_down=1&pz_uid=" + pz_uid + "&extra_uid=" + extra_uid + "&pass_down=" + pass_down + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			{rdelim}
		);
	{rdelim}

	function page_type_change(pt_uid) {ldelim}
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{ldelim}
				method: 'get',
				parameters: "extra_page_type_change=1&pt_uid=" + pt_uid + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			{rdelim}
		);
	{rdelim}

	function pagezone_type_change(pzt_uid) {ldelim}
		var request = new top.Ajax.Request(
 			top.path + "typo3conf/ext/newspaper/mod3/index.php",
 			{ldelim}
				method: 'get',
				parameters: "extra_pagezone_type_change=1&pzt_uid=" + pzt_uid + "&no_cache=" + new Date().getTime(),
				onCreate: processing,
				onSuccess: reload
			{rdelim}
		);
	{rdelim}


	// new at top = show input field for paragraph
	function subModalExtraInsertAfter(origin_uid, pz_uid, paragraph, new_at_top) {ldelim}
		var width = Math.min(700, top.getViewportWidth() - 100); 
		var height = top.getViewportHeight() - 50;
		top.showPopWin(
			top.path + "typo3conf/ext/newspaper/mod3/index.php?chose_extra=1&origin_uid=" + origin_uid + "&pz_uid=" + pz_uid + "&paragraph=" + paragraph + "&new_at_top=" + new_at_top + "&returnUrl=" + top.path + "typo3conf/ext/newspaper/mod3/close.html",
			width, 
			height, 
			null, 
			true
		);

	{rdelim}
	function extra_insert_after(origin_uid, pz_uid, paragraph, new_at_top) {ldelim}
/// \todo: add be_mode
		subModalExtraInsertAfter(origin_uid, pz_uid, paragraph, new_at_top);
	{rdelim}


	function subModalExtraEdit(table, uid) {ldelim}
		var width = Math.min(700, top.getViewportWidth() - 100); 
		var height = top.getViewportHeight() - 50;
		top.showPopWin(
			top.path + "typo3/alt_doc.php?returnUrl=" + top.path + "typo3conf/ext/newspaper/mod3/close.html&edit[" + table + "][" + uid + "]=edit",
			width, 
			height, 
			null, 
			true
		);
	{rdelim}

	function extra_edit(table, uid) {ldelim}
/// \todo: add be_mode
		subModalExtraEdit(table, uid);
	{rdelim}


	function processing() {ldelim}

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
	{rdelim}
	
	// direct reload (no popup/modalbox involved ...)
	function reload(data) {ldelim}
		if (data.responseText == '') {ldelim}
			// empty string, so no error message
/// \todo: currently no_cache param are ADDED, must be REPLACED
			self.location.href = self.location.href + "&no_cache=" + new Date().getTime();
		{rdelim} else {ldelim}
			// display error message
			self.document.getElementsByTagName('body')[0].innerHTML = data.responseText;
		{rdelim}
	{rdelim}

</script>