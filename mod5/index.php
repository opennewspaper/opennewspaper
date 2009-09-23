<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

 
$LANG->includeLLFile('EXT:newspaper/mod5/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Dashboard' for the 'newspaper' extension.
 *
 * @author	Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 */
class  tx_newspaper_module5 extends t3lib_SCbase {
	
	const number_of_latest_articles = 10;
	const shortcut_group = 5;
	
	var $pageinfo;

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();

		/*
		if (t3lib_div::_GP('clear_all_cache'))	{
			$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					// a valid page for permissions check is needed - use newspaper root folder
					$this->id = tx_newspaper_Sysfolder::getInstance()->getPidRootfolder(); 

					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;

				
					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
//debug(t3lib_div::_GP('type4newarticle'));
//debug(t3lib_div::_GP('section'));
//debug(t3lib_div::_GP('articletype'));
//debug($_REQUEST);

						$this->checkIfNewArticle();

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;
						$this->doc->form='<form action="" method="post" enctype="multipart/form-data">';

							// JavaScript
						$this->doc->JScode = '
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
							</script>
						';
						$this->doc->postCode='
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = 0;
							</script>
						';

						$headerSection = ''; //$this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


//						// ShortCut
//						if ($BE_USER->mayMakeShortcut())	{
//							$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
//						}

						$this->content.=$this->doc->spacer(10);
					} else {
							// If no access or if ID == zero

						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->spacer(10);
					}
				
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{
		$this->content .= $this->doc->section('', $this->renderBackendSmarty(), 0, 1);
	}
		
		
		
	private function renderBackendSmarty() {
		global $LANG;

		
 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/'));

		$label['new_article'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_new_article', false);
		$label['new_article_button'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_new_article_button', false);
		$label['new_article_typo3'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_new_article_typo3', false);
		$label['section'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_section', false);
		$label['articletype'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_articletype', false);
	
	
/// \todo: title flags ...	
		$smarty->assign('WIZARD_ICON', tx_newspaper_BE::renderIcon('gfx/wizard_rte2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.edit_article', false)));
		$smarty->assign('MANAGE_USERCOMMENTS_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.edit_article', false)));
		$smarty->assign('SHORTCUT_BE_ICON', tx_newspaper_BE::renderIcon('gfx/turn_right.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.edit_article', false)));
		$smarty->assign('SHORTCUT_NEWSPAPER_ICON', tx_newspaper_BE::renderIcon('gfx/turn_right.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.edit_article', false)));
	
	
		$message['demo'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_demo', false);

		$smarty->assign('LABEL', $label);
		$smarty->assign('MESSAGE', $message);

		/// latest articles		
 		$smarty_article = new tx_newspaper_Smarty();
		$smarty_article->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/'));
		$smarty_article->assign('ARTICLE', $this->getLatestArticles());
		$smarty_article->assign('ARTICLE_EDIT_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.edit_article', false)));
/// \todo: write function for path creation!!!!
		$smarty_article->assign('T3PATH', '/' . substr(PATH_typo3, strlen($_SERVER['DOCUMENT_ROOT']))); // path to typo3, needed for edit article (form: /a/b/c/typo3/)
		$smarty->assign('ARTICLELIST', $smarty_article->fetch('mod5_latestarticles.tmpl'));


		/// sys_be_shortcut
		$smarty->assign('SHORTCUT', $this->getShortcuts());



/// \todo:		$new_article = x::getRegisteredSources()
		$new_article = array(new source_demo1(), new source_demo2());
		$smarty->assign('IMPORT_SOURCE', $new_article);
		
		$smarty->assign('ARTICLETYPE', tx_newspaper_ArticleType::getArticleTypes());
		
		$smarty->assign('SECTION', tx_newspaper_Section::getAllSections());

		$smarty->assign('MODULE_PATH', TYPO3_MOD_PATH); // path to typo3, needed for edit article (form: /a/b/c/typo3/)
		
		return $smarty->fetch('mod5.tmpl');
	}		
		
	
	private function getShortcuts() {
		return tx_newspaper::selectRows(
			'*',
			'sys_be_shortcuts',
			'userid=' . $GLOBALS['BE_USER']->user['uid'] . ' AND sc_group = ' . self::shortcut_group,
			'',
			'sorting'
		);
	}	
		
		
	/// \return array of latest tx_newspaper_article's
	private function getLatestArticles() {
/// \todo: set limit per tsconfig or for each user individually
/// \todo: move to tx_newspaper_article?
		
		$row = tx_newspaper::selectRows(
			'uid',
			'tx_newspaper_article',
			'NOT is_template AND NOT deleted',
			'',
			'tstamp DESC',
			self::number_of_latest_articles
		);
	
		$article = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$article[] = new tx_newspaper_Article(intval($row[$i]['uid']));
		}
		
		return $article;
		
	}
		
		
	private function checkIfNewArticle() {
		$type4newarticle = t3lib_div::_GP('type4newarticle');
		$section = intval(t3lib_div::_GP('section'));
		$articletype = intval(t3lib_div::_GP('articletype'));
//debug(array($type4newarticle, $section, $articletype));	
		if ((strlen($type4newarticle) == 0) || $section <= 0 || $articletype <= 0)
			return false;
	  
		/// so a new article should be created
		
		if ($type4newarticle == 'newarticle') {
			/// just a plain typo3 article, no import ('newarticle' is set as a convention for this case)
			$s = new tx_newspaper_Section($section);
			$at = new tx_newspaper_ArticleType($articletype);
			
			$new_article = $s->copyDefaultArticle($at->getTSConfigSettings('musthave'));
			
			$new_article->setAttribute('articletype_id', $articletype);

			// add creation date and user
			$new_article->setAttribute('crdate', time());
			$new_article->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);

			$new_article->store();

/// \todo: muss-extras anlegen
//debug($at->getTSConfigSettings(), '####');
//t3lib_div::debug($_SERVER); die();
			$path2installation = substr(PATH_site, strlen($_SERVER['DOCUMENT_ROOT']));

			$url = $path2installation . '/typo3/alt_doc.php?returnUrl=' . $path2installation . '/typo3conf/ext/newspaper/mod5/returnUrl.php&edit[tx_newspaper_article][' . $new_article->getUid() . ']=edit';
			
			header('Location: ' . $url);		
			 
		} else {
			die('import new article from source');	
		}
		
		
		
		
	}	
		
		
				
		}











if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod5/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod5/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_newspaper_module5');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();




/// \todo: remove after testing
class source_demo1 {
	function getTitle() {
		return 'Import from RedSys';
	}	
	function getClass() {
		return get_class($this);
	}	
}
class source_demo2 {
	function getTitle() {
		return 'Import from archive';
	}	
	function getClass() {
		return get_class($this);
	}	
}

?>