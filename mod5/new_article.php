<?php

include('index.php');

$module = new tx_newspaper_module5();

	function checkIfNewArticle() {
		$type = t3lib_div::_GP('type');
		$section = intval(t3lib_div::_GP('section'));
		$articletype = intval(t3lib_div::_GP('articletype'));
//debug(array($type4newarticle, $section, $articletype));	
		if ((strlen($type) == 0) || $section <= 0 || $articletype <= 0)
			return false;
	  
		/// so a new article should be created
		
		if ($type == 'newarticle') {
			$this->createNewArticle($section, $articletype);
		} else {
			$this->importArticle($section, $articletype, $type);
		}
	
	}
	
	function createNewArticle($section, $articletype) {
		/// just a plain typo3 article, no import ('newarticle' is set as a convention for this case)
		$s = new tx_newspaper_Section($section);
		$at = new tx_newspaper_ArticleType($articletype);
			
		$new_article = $s->copyDefaultArticle($at->getTSConfigSettings('musthave'));
t3lib_div::devlog('at tsc musthave', 'newspaper', 0, $at->getTSConfigSettings('musthave'));
t3lib_div::devlog('at tsc shouldhave', 'newspaper', 0, $at->getTSConfigSettings('shouldhave'));			
		$new_article->setAttribute('articletype_id', $articletype);

		// add creation date and user
		$new_article->setAttribute('crdate', time());
		$new_article->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);

		$new_article->store();

		$path2installation = substr(PATH_site, strlen($_SERVER['DOCUMENT_ROOT']));

		/*	volle URL muss angegeben werden, weil manche browser sonst 
		 *  'http://' davorhaengen.
		 */			
		$url_parts = explode('/typo3', tx_newspaper::currentURL());
		$base_url = $url_parts[0];

		$url = $base_url . '/typo3/alt_doc.php?returnUrl=' . $path2installation .
				'/typo3conf/ext/newspaper/mod5/returnUrl.php&edit[tx_newspaper_article][' .
				$new_article->getUid() . ']=edit';
/*
			$url = $path2installation . '/typo3/alt_doc.php?returnUrl=' .
			 	   $path2installation . '/typo3conf/ext/newspaper/mod5/returnUrl.php&edit[tx_newspaper_article][' .
			 	   $new_article->getUid() . ']=edit';
*/
		header('Location: ' . $url);				
	}
	
	function importArticle($section, $articletype, $source_id) {
		$source = tx_newspaper::getRegisteredSource($source_id);
		
		$this->browse_path = $source->browse(new tx_newspaper_SourcePath(''));
		t3lib_div::devlog('/', 'newspaper', 0, $this->browse_path);
		
		# $this->moduleContent();
		#$this->renderBackendSmarty();
		
		# die('import new article from source '.$source->getTitle());			
	}
	
	function browse_path() {
		$this->browse_path = $source->browse(new tx_newspaper_SourcePath($_REQUEST['browse_path']));
		
	}

?>