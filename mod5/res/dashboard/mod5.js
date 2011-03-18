{* This file is designed to be include()d by a Smarty template, not <script>ed
   from a HTML page
*}

<script language="javascript">
		
	/// when section is changed the assigned default article type gets selected
	function setDefaultArticletype(section_id) {ldelim}
		switch(parseInt(section_id)) {ldelim}
			{foreach item=sub_section from=$SECTION2}
				{foreach item=section from=$sub_section}
					{if $section->getAttribute('default_articletype') > 0}
						case {$section->getUid()}:
							d_at = {$section->getAttribute('default_articletype')};
						break;
					{/if}
				{/foreach}
			{/foreach}
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
      document.getElementById('source_browser').style.display = 'none';
	  document.getElementById('article_text').style.display = 'none';
	  document.getElementById('source_browser').innerHTML = '';
	  document.getElementById('article_text').innerHTML = '';
      changeLoremButtonDisplayOption('inline');
	{rdelim}

	function updateSourceBrowser(response) {ldelim}
      changeLoremButtonDisplayOption('none');
      document.getElementById('source_browser').style.display = 'inline';
	  document.getElementById('article_text').style.display = 'inline';
	  document.getElementById('source_browser').innerHTML = response.responseText;
      document.getElementById('article_text').innerHTML = '';
	{rdelim}

    function changeLoremButtonDisplayOption(displayOption) {ldelim}
        if(document.getElementById('lorem')) {ldelim}
           document.getElementById('lorem').style.display = displayOption
        {rdelim}
    {rdelim}
	
	function setArticlePreview(response)  {ldelim}
      	document.getElementById('article_text').style.display = 'inline';
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

	    document.getElementById('source_browser').style.display = 'inline';
	    document.getElementById('article_text').style.display = 'inline';

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
	  
	  
{literal}
	var section2_uid_old = null;
	// show section selectbox associated with chosen section in selectbox 1 (hide previously displayed sub section selectbox, if any)
	function changeSectionBox2(section_uid) {
		if (section2_uid_old != null) {
			// hide previously chosen section selectbox
			document.getElementById('section2_' + parseInt(section2_uid_old)).style.display = 'none';
			document.getElementById('section2_' + parseInt(section2_uid_old)).name = '';
		} else {
			// hide empty selectbox
			document.getElementById('section2').style.display = 'none';
			document.getElementById('section2').name = '';
		}
		section2_uid_old = section_uid;
		// display chosen section selectbox
		document.getElementById('section2_' + parseInt(section_uid)).style.display = 'inline';
		document.getElementById('section2_' + parseInt(section_uid)).name = 'tx_newspaper_mod5[section]'; // currently active section list gets this name in order to show the article wizard where to check ...
		
//console.log(document.getElementById('section2_' + parseInt(section_uid)).value);
		if (document.getElementById('section2_' + parseInt(section_uid)).value != -1) {
			setDefaultArticletype(document.getElementById('section2_' + parseInt(section_uid)).value);
		}
		
	}

{/literal}
	  
</script>