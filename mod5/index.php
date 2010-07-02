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

require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:newspaper/mod5/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]


/// Class to generate a BE module with 100% width
class fullWidthDoc_mod5 extends template {
	var $divClass = 'typo3-fullWidthDoc';	///< Sets width to 100%
}


/**
 * Module 'Wizards' for the 'newspaper' extension.
 *
 * @author	Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 */
class  tx_newspaper_module5 extends t3lib_SCbase {
	
	const prefixId = 'tx_newspaper_mod5';
	
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

		// Access check!
		$access = $BE_USER->user['uid']? true : false; // \todo: better check needed

		if ($access) {

			// get "pi"vars
			$input = t3lib_div::GParrayMerged('tx_newspaper_mod5');
//t3lib_div::devlog('mod5 main()', 'newspaper', 0, array('input' => $input, '_request' => $_REQUEST));
			switch ($input['ajaxcontroller']) {
				case 'browse_path' :
					die($this->browse_path($input));
				case 'load_article' :
					die($this->load_article($input));
// \todo: Helge, still needed?
//				case 'import_article' :
//					die($this->import_article($input));
				case 'change_role': 
					$this->changeRole($input); // no die() needed, just change the role and re-render the module
				break;
			}
			
			// Draw the header.
			$this->doc = t3lib_div::makeInstance('fullWidthDoc_mod5');
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


			switch ($input['controller']) {
				case 'new_article_wizard': 
					$this->new_article_backend(); // fills $this->doc with the new article wizard backend
				break;
				case 'new_article_create':
				case 'new_article_create_dummy':
					// create/import new article and redirect to article backend
					switch($input['type']) {
						case 'newarticle':
							// "normal" new article
							$this->createNewArticle($input);
						break;
						default:
							// "imported" article
							$this->import_article($input);
					}
				break;
				default: 
					$this->moduleContent(); // Render start wizard page
			}		

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
		$label['wizards'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_wizards', false);
		$label['latest_articles'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_latest_articles', false);
		$label['shortcuts'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_shortcuts', false);
		$label['manage_usercomments'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_manage_usercomments', false);
		$label['newspaper_functions'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_newspaper_functions', false);
	
		$smarty->assign('WIZARD_ICON', tx_newspaper_BE::renderIcon('gfx/wizard_rte2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_start_wizard', false)));
		$smarty->assign('MANAGE_USERCOMMENTS_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_usercomments', false)));
		$smarty->assign('SHORTCUT_BE_ICON', tx_newspaper_BE::renderIcon('gfx/turn_right.gif', '', $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_shortcut_typo3', false)));
		$smarty->assign('SHORTCUT_NEWSPAPER_ICON', tx_newspaper_BE::renderIcon('gfx/turn_right.gif', '', $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_shortcut_newspaper', false)));
		$smarty->assign('ROLE_ICON', tx_newspaper_BE::renderIcon('gfx/i/be_users.gif', '', $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_role', false)));
	
	
		$message['demo'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_demo', false);

		$smarty->assign('LABEL', $label);
		$smarty->assign('MESSAGE', $message);

		/// newspaper roles
		$role = tx_newspaper_workflow::getRole();
		$changeto_value = ($role == NP_ACTIVE_ROLE_DUTY_EDITOR)? NP_ACTIVE_ROLE_EDITORIAL_STAFF : NP_ACTIVE_ROLE_DUTY_EDITOR; // 
		$smarty->assign('ROLE', array(
			'current' => tx_newspaper_workflow::getRoleTitle($role),
			'changeto' => tx_newspaper_workflow::getRoleTitle($changeto_value),
			'changeto_value' => $changeto_value 
		));


		/// latest articles		
 		$smarty_article = new tx_newspaper_Smarty();
		$smarty_article->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/'));
		$smarty_article->assign('ARTICLE', $this->getLatestArticles());
		$smarty_article->assign('ARTICLE_EDIT_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.edit_article', false)));
		$smarty_article->assign('T3PATH', tx_newspaper::getAbsolutePath() . 'typo3/'); // path to typo3, needed for edit article
		$smarty->assign('ARTICLELIST', $smarty_article->fetch('mod5_latestarticles.tmpl'));


		/// sys_be_shortcut
		$smarty->assign('SHORTCUT', $this->getShortcuts());


		$sources = tx_newspaper::getRegisteredSources();
		$smarty->assign('IMPORT_SOURCE', $sources);
		
		$smarty->assign('ARTICLETYPE', tx_newspaper_ArticleType::getArticleTypes());
		
//		$smarty->assign('SECTION', tx_newspaper_Section::getAllSections());

		if ($this->browse_path) {
			$smarty->assign('BROWSE_PATH', $this->browse_path);
		}

		$smarty->assign('MODULE_PATH', tx_newspaper::getAbsolutePath() . 'typo3conf/ext/newspaper/mod5/'); // path to typo3, needed for edit article (form: /a/b/c/typo3/)
		
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
	
	/// render new article wizard backend
	private function new_article_backend() {
//t3lib_div::devlog('NEW ARTICLE', 'newspaper', 0);		
		global $LANG;
		
 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/'));

		$smarty->assign('LABEL', array(
			'new_article' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_new_article', false),
			'new_article_button' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_new_article_button', false),
			'new_article_typo3' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_new_article_typo3', false),
			'section' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_section', false),
			'section_base' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_section_base', false),
			'section_select' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_section_select', false),
			'articletype' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_articletype', false),
			'back_to_wizards' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_back_to_wizards', false),
			'error_browsing' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_error_browsing', false),
			'no_sect' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_error_browsing', false),
		));

		$smarty->assign('MESSAGE', array(
			'no_section' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:message_no_section', false),
			'no_articletype' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:message_no_articletype', false),
			'no_section_chosen' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:message_no_section_chosen', false),
		));		

		$smarty->assign('IS_ADMIN', $GLOBALS['BE_USER']->user['admin']);
		$smarty->assign('SHOW_LOREM', ($GLOBALS['BE_USER']->getTSConfigVal('tx_newspaper.use_lorem') != 0));

		$sources = tx_newspaper::getRegisteredSources();
		$smarty->assign('IMPORT_SOURCE', $sources);
		
		$smarty->assign('ARTICLETYPE', tx_newspaper_ArticleType::getArticleTypes());

		// \todo: TSConfig + more than 1 start section
		$start_section = new tx_newspaper_section(1); // ATTENTION: 1 is hard coded section "Start" !!!
		$start_sections = $start_section->getChildSections(false);
		
		$sub_sections = array();
		foreach($start_sections as $key => $current_sub_section) {
			// check if main section on level 1 can take articles. add to section2 if yes (only selectbox2 sections can be chosen)
			if ($current_sub_section->getAttribute('articles_allowed')) {
				$sub_sections[$current_sub_section->getUid()][$start_section->getUid()] = $current_sub_section;
			}
			// add all sub sections that can take articles
			$tmp_sections = $current_sub_section->getChildSections(true);
			foreach($tmp_sections as $tmp_section) {
				// check if section can take articles. add to section2 if yes (only selectbox2 sections can be chosen)
				if ($tmp_section->getAttribute('articles_allowed')) {
					$sub_sections[$current_sub_section->getUid()][$tmp_section->getUid()] = $tmp_section;
				}
			}
			// if no sub section could be found for a start section, remove start section (if main section is allowed to take articles, the sub section IS NOT empty)
			if (sizeof($sub_sections[$current_sub_section->getUid()]) == 0) {
				unset($start_sections[$key]); // no sub section for this base section, so do not list this base section
			}
		}
		
		$smarty->assign('SECTION1', $start_sections);
		$smarty->assign('SECTION2', $sub_sections);
//t3lib_div::devlog('new article wizard', 'newspaper', 0, array('start_sections' => $start_sections, 'sub_sections' => $sub_sections));

		if ($this->browse_path) {
			$smarty->assign('BROWSE_PATH', $this->browse_path);
		}

		$smarty->assign('MODULE_PATH', tx_newspaper::getAbsolutePath() . 'typo3conf/ext/newspaper/mod5/'); // path to typo3, needed for edit article (form: /a/b/c/typo3/)
		
		$this->content .= $this->doc->section('', $smarty->fetch('mod5_newarticle.tmpl'), 0, 1);
		$this->content.=$this->doc->spacer(10);
		
	}
	
	/// creates a new article
	private function createNewArticle($input) { 
		/// just a plain typo3 article
		$s = new tx_newspaper_Section($input['section']);
		$at = new tx_newspaper_ArticleType($input['articletype']);
			
		$new_article = $s->createNewArticle($at);
//t3lib_div::devlog('at tsc musthave', 'newspaper', 0, $at->getTSConfigSettings('musthave'));
//t3lib_div::devlog('at tsc shouldhave', 'newspaper', 0, $at->getTSConfigSettings('shouldhave'));			
		$new_article->setAttribute('articletype_id', $input['articletype']);

		// add creation date and user
		$new_article->setAttribute('crdate', time());
		$new_article->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
		$new_article->setAttribute('hidden', 1); // hide new article

		if ($input['controller'] == 'new_article_create_dummy') {
			// add some dummy content
			$new_article->setAttribute('kicker', 'Kicker ' . uniqid());
			$new_article->setAttribute('title', 'Title ' . uniqid());
			$new_article->setAttribute('teaser', tx_newspaper_be::getLoremIpsum());
			$new_article->setAttribute('text', tx_newspaper_be::getLoremIpsum(rand(1, 3), true, false));
		}

		$new_article->store();

		$base_url = tx_newspaper::getAbsolutePath();

		$url = $base_url . 'typo3/alt_doc.php?returnUrl=' . $base_url .
				'typo3conf/ext/newspaper/mod5/returnUrl.php&edit[tx_newspaper_article][' .
				$new_article->getUid() . ']=edit';
		header('Location: ' . $url);				
	}
	
	function browse_path(array $input) {
t3lib_div::devlog('browse_path', 'newspaper', 0, array('input' => $input));
		$source_id = $input['source_id'];
		$path = $input['path'];
		$source = tx_newspaper::getRegisteredSource($source_id);
		
		$width = (intval($GLOBALS['BE_USER']->getTSConfigVal('tx_newspaper.article_source.browser_width')) > 0)? intval($GLOBALS['BE_USER']->getTSConfigVal('tx_newspaper.article_source.browser_width')) : 430; // 430px is default
		
		$ret = '<select name="' . $this->prefixId . 'source_path" size="10" style="width: ' . $width . 'px; float: left; margin-right: 16px; height: 400px;">' . "\n";
 		$ret .= '<option onclick=changeSource(\'' . $source_id . '\',\'\')' . '>Top</option>' . "<br />\n";
		$ret .= '<option onclick=changeSource(\'' . $source_id . '\',\'' . 'Reload ' . $path . '\')' . '>' . 
				$path . '</option>' . "<br />\n";
		
		foreach ($source->browse(new tx_newspaper_SourcePath($path)) as $entry) {
			if ($entry->isText()) {
				$ret .= 
				    '<option title="' . utf8_encode($entry->getTitle()) . 
				          '" onclick=loadArticle(\'' . $source_id . '\',\'' . $entry->getID() .'\')' . '>' . 
				        utf8_encode($entry->getTitle()) .
				        '     <strong>[' . $source->getProductionStatus($entry) . ']</strong>' . 
				    '</option>' . "\n";
			} else {
				$ret .= 
				    '<option title="' . utf8_encode($entry->getTitle()) . 
				          '" onclick=changeSource(\'' . $source_id . '\',\'' . $entry->getID() .'\')' . '>' . 
				        utf8_encode($entry->getTitle()) . 
				    '</option>' . "\n";
			}  
		}
		$ret .= '</select>' . "<br />\n";
		
		die($ret);
	}
	
	function load_article(array $input) {
		$source_id = $input['source_id'];
		$path = $input['path'];
		$source = tx_newspaper::getRegisteredSource($source_id);
		$article = new tx_newspaper_Article();
		$source->readFields($article, 
							array('title', 'teaser', 'text'), 
							new tx_newspaper_SourcePath($path));

		$import_info = '<input type="hidden" name="' . $this->prefixId . 'source_id" value="' . $source_id . '" />' .
					   '<input type="hidden" name="' . $this->prefixId . 'source_path" value="' . $path . '" />';
		
		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/'));
		$smarty->assign('article', $article);
		$smarty->assign('source_id', $source_id);
		$smarty->assign('source_path', $path);
		
		die($smarty->fetch('mod5_articlepreview.tmpl'));
	}

	function import_article(array $input) {

		$section = new tx_newspaper_Section(intval($input['section']));
		$articletype = new tx_newspaper_ArticleType(intval($input['articletype']));

		$source = tx_newspaper::getRegisteredSource($input['source_id']);
		$path = new tx_newspaper_SourcePath($input['source_path']);
		
		t3lib_div::devlog('import_article', 'newspaper', 0, 
			array(
				'$input' => $input,
				'$section' => $section,
				'$articletype' => $articletype,
				'$source_id' => $input['source_id'],
				'$path' => $input['source_path'],
			)
		);
		
		$new_article = $this->createAndImportArticle($articletype, $section, $source, $path);

		$this->logImport($new_article, $input);
		
		$this->redirectToArticleMask($new_article);
	}

	/// Create an article of requested type, perform the import, set necessary attributes and store the article
	/** This function violates the "do one thing" rule clearly... anyway, still
	 *  better than leaving everything in import_article(). 
	 * 
	 * @param $type    the selected article type.
	 * @param $section section the article belogs to - needed for the default extras.
	 * @param $source  source the article is imported from.
	 */
	private function createAndImportArticle(tx_newspaper_ArticleType $type, 
	                                        tx_newspaper_Section $section, 
	                                        tx_newspaper_Source $source, 
	                                        tx_newspaper_SourcePath $path) {
	                                   
        $new_article = $section->createNewArticle($type);
        $new_article->setAttribute('articletype_id', $type->getUid());

        $source->readArticle($new_article, $path);
        
        // add creation date and user
        $new_article->setAttribute('crdate', time());
        $new_article->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
        $new_article->setAttribute('hidden', 1); // hide imported article

        $new_article->store();
		
        return $new_article;
	}
	
	/// Note import parameters in workflow log for \p $new_article.
	private function logImport(tx_newspaper_Article $new_article, array $input) {
        $comment = $GLOBALS['LANG']->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_import', false);
        if ($input['source_id']) {
            $comment .= ', ' . $GLOBALS['LANG']->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_import_source_id', false) . ': ' . $input['source_id'];
        }
        if ($input['source_path']) {
            $comment .= ', ' . $GLOBALS['LANG']->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_import_source_path', false) . ': ' . $input['source_path'];
        }
        tx_newspaper_Workflow::directLog('tx_newspaper_article', $new_article->getUid(), $comment, NP_WORKLFOW_LOG_IMPORT);
		
	}
	
	/// Redirect the browser to the article mask for further editing after the import.
	private function redirectToArticleMask(tx_newspaper_Article $new_article) {
        $path2installation = substr(PATH_site, strlen($_SERVER['DOCUMENT_ROOT']));

        /*  volle URL muss angegeben werden, weil manche browser sonst 
         *  'http://' davorhaengen.
         */         
        $url_parts = explode('/typo3', tx_newspaper::currentURL());
        $base_url = $url_parts[0];

        $url = $base_url . '/typo3/alt_doc.php?returnUrl=' . $path2installation .
                '/typo3conf/ext/newspaper/mod5/returnUrl.php&edit[tx_newspaper_article][' .
                $new_article->getUid() . ']=edit';
                
        header('Location: ' . $url); // redirect to article backend 
	} 
	
	private function changeRole(array $input) {
//t3lib_div::devlog('changeRole()', 'newspaper', 0, array('input' => $input));
		tx_newspaper_workflow::changeRole(intval($input['new_role']));
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


?>