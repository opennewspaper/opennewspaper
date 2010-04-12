{* This file is designed to be include()d by a Smarty template, not <script>ed
   from a HTML page
*}

<script language="javascript">
		
	/// when section is changed the assigned default article type gets selected
	function setDefaultArticletype(section_id) {ldelim}
		switch(parseInt(section_id)) {ldelim}
			{section name=i loop=$SECTION}
				{if $SECTION[i]->getAttribute('default_articletype') > 0}
					case {$SECTION[i]->getUid()}:
						d_at = {$SECTION[i]->getAttribute('default_articletype')};
					break;
				{/if}
			{/section}
			default:
				d_at = 0;
		{rdelim}
		if (d_at > 0) {ldelim}
			document.getElementById('articletype').value = d_at;
		{rdelim}
	{rdelim}
	
	/// "processing spinner" in source_browser div
	function processing() {ldelim}
		document.getElementById('source_browser').innerHTML = '<img src="' + top.path + 'typo3/gfx/spinner.gif"/>';
	{rdelim}

	/// "processing spinner" in article_text div
	function processing_preview() {ldelim}
		document.getElementById('article_text').innerHTML = '<img src="' + top.path + 'typo3/gfx/spinner.gif"/>';
	{rdelim}

	/// Error message in source_browser div
	function failed() {ldelim}
		document.getElementById('source_browser').innerHTML = '<span color="ff0000">{$LABEL.error_browsing}</span>';
	{rdelim}

	function clearSourceBrowser() {ldelim}
      document.getElementById('source_browser').innerHTML = '';
	  document.getElementById('article_text').innerHTML = '';
	  document.getElementById('lorem').style.display = 'inline';
	{rdelim}

	function updateSourceBrowser(response) {ldelim}
      document.getElementById('lorem').style.display = 'none';
	  document.getElementById('source_browser').innerHTML = response.responseText;
      document.getElementById('article_text').innerHTML = '';
	{rdelim}
	
	function setArticlePreview(response)  {ldelim}
      	document.getElementById('article_text').innerHTML = response.responseText;
	{rdelim}
	
	function importArticle(source_id, path) {ldelim}
		var params = 'tx_newspaper_mod5[ajaxcontroller]=import_article&tx_newspaper_mod5[source_id]='+source_id+'&tx_newspaper_mod5[path]='+path;
	    var request = new top.Ajax.Request(
	      top.path + "typo3conf/ext/newspaper/mod5/index.php",
		  {ldelim}
		    method:'get', 
		    parameters: params,
			onCreate: processing,
		    onSuccess: setArticlePreview,
		    onFailure: failed
		  {rdelim}
	    );
		alert('importArticle()');
	{rdelim}
		
	function loadArticle(source_id, path) {ldelim}

		var params = 'tx_newspaper_mod5[ajaxcontroller]=load_article&tx_newspaper_mod5[source_id]='+source_id+'&tx_newspaper_mod5[path]='+path;

	    var request = new top.Ajax.Request(
	      top.path + "typo3conf/ext/newspaper/mod5/index.php",
		  {ldelim}
		    method:'get', 
		    parameters: params,
			onCreate: processing_preview,
		    onSuccess: setArticlePreview,
		    onFailure: failed
		  {rdelim}
	    );
	{rdelim}
	
	function changeSource(source_id, path) {ldelim}
		var params = 'tx_newspaper_mod5[ajaxcontroller]=browse_path&tx_newspaper_mod5[source_id]='+source_id+'&tx_newspaper_mod5[path]='+path;

	    var request = new top.Ajax.Request(
	      top.path + "typo3conf/ext/newspaper/mod5/index.php",
		  {ldelim}
		    method:'get', 
		    parameters: params,
			onCreate: processing,
		    onSuccess: updateSourceBrowser,
		    onFailure: failed
		  {rdelim}
	    );
	  {rdelim}
</script>
