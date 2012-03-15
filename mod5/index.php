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
 * Module 'Webmaster' for the 'newspaper' extension.
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
//t3lib_div::devlog('case load_article', 'newspaper', 0, array('input' => $input, '_request' => $_REQUEST));
					$response = $this->load_article();
					die($response);
				case 'change_role':
					$this->changeRole($input); // no die() needed, just change the role and re-render the module
				break;
				case 'CtrlTagCat':
					die($this->getControlTagsForCtrlTagType($input));
				break;
			}

			// Draw the header.
			$this->doc = t3lib_div::makeInstance('fullWidthDoc_mod5');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="post" enctype="multipart/form-data" onsubmit="return false;">'; // don't submit form when enter is pressed

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
					$this->new_article_backend($input); // fills $this->doc with the new article wizard backend
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
				case 'w_pz':
					// wizard: activate/de-activate pagezones
					$this->processWizardPagezone($input);
				break;
				case 'w_inheritance':
					// wizard: set inheritance source for pagezones
					$this->processWizardInheritanceSource($input);
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
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/res/dashboard/'));

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
		$label['webmaster_wizards'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_webmaster_wizards', false);
		$label['webmaster_wizards_tsconfig'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_webmaster_wizards_tsconfig', false);
		$label['webmaster_wizard_pagezone'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_webmaster_wizard_pagezone', false);
		$label['webmaster_wizard_inheritance'] = $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_webmaster_wizard_inheritance', false);

		$smarty->assign('WIZARD_ICON', tx_newspaper_BE::renderIcon('gfx/wizard_rte2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_start_wizard', false)));
		$smarty->assign('MANAGE_USERCOMMENTS_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_usercomments', false)));
		$smarty->assign('SHORTCUT_BE_ICON', tx_newspaper_BE::renderIcon('gfx/turn_right.gif', '', $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_shortcut_typo3', false)));
		$smarty->assign('SHORTCUT_NEWSPAPER_ICON', tx_newspaper_BE::renderIcon('gfx/turn_right.gif', '', $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_shortcut_newspaper', false)));
		$smarty->assign('ROLE_ICON', tx_newspaper_BE::renderIcon('gfx/i/be_users.gif', '', $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_role', false)));

		$smarty->assign('WIZARD_PERMISSION', $this->getTsconfigForWebmasterWizards());

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
		$smarty_article->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/res/dashboard/'));
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

		$smarty->assign('IS_ADMIN', $GLOBALS['BE_USER']->user['admin']);

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


	/// wizard functions


	/** User TSCofnig newspaper.webmasterWizards
	 * \return Array with webmaster wizard access permissions (key = webmaster wizard key, value=1 -> permissions granted)
	 */
	private function getTsconfigForWebmasterWizards() {

		if (!$tsc = $GLOBALS['BE_USER']->getTSConfigVal('newspaper.webmasterWizards')) {
			return false;
		}

		$perms = array();
		foreach(t3lib_div::trimExplode(',', $tsc) as $key => $value) {
			$perms[$value] = 1;
		}

		return $perms;
	}


	/** Renders/executes wizard: activate/de-activate pagezones
	 *  \param $input array of get params formed like tx_nwespaper_mod5[...]
	 *  \return Wizard page (steps within wizard or success message) (and processes commands)
	 */
	private function processWizardPagezone(array $input) {
//t3lib_div::devlog('processWizardPagezone()', 'newspaper', 0, array('input' => $input));

		$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod5/locallang.xml', $GLOBALS['LANG']->lang);

		// render basic form / display chosen page type and pagezone type
		$backend = $this->renderWizardPagezoneSelector($input);

		if (isset($input['pagetype_uid']) && isset($input['pagezonetype_uid'])) {
			$pagetype = new tx_newspaper_pagetype(intval($input['pagetype_uid']));
			$pagezonetype = new tx_newspaper_pagezonetype(intval($input['pagezonetype_uid']));
			if (!isset($input['action'])) {
				// so a pagezone type is chosen, start specific wizard
				$smarty = new tx_newspaper_Smarty();
				$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/res/activate_pz/'));
				$smarty->assign('LL', $localLang[$GLOBALS['LANG']->lang]);
				$smarty->assign('input', $input);
				$backend .= $smarty->fetch('mod5_wizard_action_pagezone.tmpl');
			} else {
				// action is chosen, so perform action now ...
				if ($input['action'] == 'activatePz') {
					// activate ...
					foreach(tx_newspaper_section::getRootSections() as $rootSection) {
						foreach($rootSection->getChildSections(true) as $s) {
							$s->activatePage($pagetype);
							$p = $s->getSubPage($pagetype);
							$p->activatePagezone($pagezonetype);
						}
					}
					// insert backend success message
					$backend = $localLang[$GLOBALS['LANG']->lang]['message_webmaster_wizard_pagezone_activate_success'];
				} elseif ($input['action'] == 'deactivatePz') {
					// delete ...
					foreach(tx_newspaper_section::getRootSections() as $rootSection) {
						foreach($rootSection->getChildSections(true) as $s) {
							if ($p = $s->getSubPage($pagetype)) {
								if ($pz = $p->getPagezone($pagezonetype)) {
									$pz->delete();
								}
								// \todo: delete page if last pagezone is deleted?
							}
						}
					}
					// insert backend success message
					$backend = $localLang[$GLOBALS['LANG']->lang]['message_webmaster_wizard_pagezone_deactivate_success'];
				} else {
					t3lib_div::devlog('processWizardPagezone(): Unknown action type', 'newspaper', 3, array('input' => $input));
				}
			}
		}


		if (isset($input['pagetype_uid']) && $input['action'] == 'deactivateP') {
			$pagetype = new tx_newspaper_pagetype(intval($input['pagetype_uid']));
			// delete pages ...
			foreach(tx_newspaper_section::getRootSections() as $rootSection) {
				foreach($rootSection->getChildSections(true) as $s) {
					if ($p = $s->getSubPage($pagetype)) {
						foreach($p->getPagezones() as $pz) {
							$pz->delete();
						}
						$p->delete();
					}
				}
			}
			// insert backend success message
			$backend = $localLang[$GLOBALS['LANG']->lang]['message_webmaster_wizard_page_deactivate_success'];
		}


		$this->content .= $this->doc->section('', $backend, 0, 1);
		$this->content.=$this->doc->spacer(10);

	}

	/** Renders/executes wizard: set inheritance source
	 * Iteration 1: Leave root sections untouched, set all subsequent section to "inherit from above"
	 *  \param $input array of get params formed like tx_nwespaper_mod5[...]
	 *  \return Wizard page (steps within wizard or success message) (and processes commands)
	 */
	private function processWizardInheritanceSource(array $input) {
//t3lib_div::devlog('processWizardInheritanceSource()', 'newspaper', 0, array('input' => $input));
		$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod5/locallang.xml', $GLOBALS['LANG']->lang);

		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/res/inheritance/'));

		$smarty->assign('LL', $localLang[$GLOBALS['LANG']->lang]);

		if (isset($input['action']) && $input['action'] == 1) {
			// run wizard
			foreach(tx_newspaper_section::getRootSections() as $rootSection) {
				// ignore root section page zone (these are used to define the inheritance sources)
				if ($rootSection) foreach($rootSection->getChildSections(true) as $s) {
					// all sub sections ...
					if ($s) foreach($s->getActivePages() as $p) {
						// all active pages ...
						if ($p) foreach($p->getActivePageZones(false) as $pz) {
							// all active pagezones ...
							$pz->changeParent(0);// set to default: inherit from same page type above
							tx_newspaper_Workflow::logPlacement($pz->getTable(), $pz->getUid(), array('newParent' => 0), NP_WORKLFOW_LOG_WEBMASTER_TOOL_INHERITANCE_SOURCE);
						}
					}
				}
			}

			$smarty->assign('SUCCESS', true);
		}

		$backend = $smarty->fetch('mod5_wizard_base.tmpl');

		$this->content .= $this->doc->section('', $backend, 0, 1);
		$this->content.=$this->doc->spacer(10);

	}

	/** Renders wizard: choose page type and pagezone type
	 *  \param $input array of get params formed like tx_nwespaper_mod5[...]
	 *  \return Wizard page (steps within wizard)
	 */
	private function renderWizardPagezoneSelector(array $input) {
//t3lib_div::devlog('renderWizardPagezoneSelector()', 'newspaper', 0, array('input' => $input));

		$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod5/locallang.xml', $GLOBALS['LANG']->lang);


		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/res/activate_pz/'));

		$smarty_sub = new tx_newspaper_Smarty();
		$smarty_sub->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/res/activate_pz/'));

		// assign labels to smarty templates
		$smarty->assign('LL', $localLang[$GLOBALS['LANG']->lang]);
		$smarty_sub->assign('LL', $localLang[$GLOBALS['LANG']->lang]);


		// get object or null
		$pagetype = (intval($input['pagetype_uid']))? new tx_newspaper_pagetype(intval($input['pagetype_uid'])) : null;
		$pagezonetype = (intval($input['pagezonetype_uid']))? new tx_newspaper_pagezonetype(intval($input['pagezonetype_uid'])) : null;

		$currentStep = '';
		if (!$pagetype) {
			// no pagetype set, so get pagetype uid in second step
			$pageTypes = tx_newspaper_pagetype::getAvailablePageTypes();
			$smarty_sub->assign('page_types', $pageTypes);
			$currentStep = $smarty_sub->fetch('mod5_wizard_pagetype.tmpl');
		} elseif (!$pagezonetype) {
			// no pagezonetype set, so get pagezonetype uid in third step
			$smarty_sub->assign('pagetype', $pagetype);
			$pagezoneTypes = tx_newspaper_pagezonetype::getAvailablePagezoneTypes(false);
			$smarty_sub->assign('pagezone_types', $pagezoneTypes);
			$smarty_sub->assign('input', $input);
			$currentStep = $smarty_sub->fetch('mod5_wizard_pagezonetype.tmpl');
		}
		$smarty->assign('currentStep', $currentStep);

		$smarty->assign('root_sections', tx_newspaper_section::getRootSections());
		$smarty->assign('pagetype', $pagetype);
		$smarty->assign('pagezonetype', $pagezonetype);

		$smarty->assign('input', $input); // add params

		$this->content .= $this->doc->section('', $smarty->fetch('mod5_wizard_base.tmpl'), 0, 1);
		$this->content.=$this->doc->spacer(10);

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
	/// \param $input paramter extracted from url
	private function new_article_backend(array $input) {
//t3lib_div::devlog('NEW ARTICLE', 'newspaper', 0, array('input' => $input));
		global $LANG;

 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/res/dashboard/'));

		$smarty->assign('LABEL', $this->getNewArticleLabels());
		$smarty->assign('MESSAGE', $this->getNewArticleMessages());

		$smarty->assign('INPUT', $input); // Add data

		$smarty->assign('IS_ADMIN', $GLOBALS['BE_USER']->user['admin']);
		$smarty->assign('SHOW_LOREM', ($GLOBALS['BE_USER']->getTSConfigVal('tx_newspaper.use_lorem') != 0));

		$sources = tx_newspaper::getRegisteredSources();
		$smarty->assign('IMPORT_SOURCE', $sources);

		$smarty->assign('ARTICLETYPE', tx_newspaper_ArticleType::getArticleTypes());

		$smarty->assign('CTRLTAGCATS', tx_newspaper_tag::getAllControltagCategories());


        // Get base sections
        $baseSections = tx_newspaper_Section::getBaseSections();


        $startSections = $this->getStartSections($baseSections);
        $targetSections = $this->getTargetSections($startSections);

		$smarty->assign('SECTION1', $startSections);
		$smarty->assign('SECTION2', $targetSections);
//t3lib_div::devlog('new article wizard', 'newspaper', 0, array('baseSections' => $baseSections, 'startSections' => $startSections, 'targetSections' => $targetSections));

		if ($this->browse_path) {
			$smarty->assign('BROWSE_PATH', $this->browse_path);
		}

		$smarty->assign('MODULE_PATH', tx_newspaper::getAbsolutePath() . 'typo3conf/ext/newspaper/mod5/'); // path to typo3, needed for edit article (form: /a/b/c/typo3/)

		$smarty->assign('DEFAULT_SOURCE', $this->getDefaultSource()); // select this radio button by default

		$this->content .= $this->doc->section('', $smarty->fetch('mod5_newarticle.tmpl'), 0, 1);
		$this->content .= $this->doc->spacer(10);

	}

    /**
     * Get all subsequent section for given start sections
     * @param array $startSections
     * @return array [uid of start section][uidS of target sections] = section object
     */
    private function getTargetSections(array $startSections) {
        $targetSections = array();
        /** @var tx_newspaper_Section $startSection */
        foreach ($startSections as $startSection) {

            // Check if start section can take articles. Add if yes.
            if ($startSection->getAttribute('show_in_list')) {
                $targetSections[$startSection->getUid()][$startSection->getUid()] = $startSection;
            }

            // Get direct children
            $childSections = $startSection->getChildSections(false);
            /** @var tx_newspaper_Section $startSection */
            foreach ($childSections as $key => $childSection) {

                // Check if child section can take articles. Add if yes.
                if ($childSection->getAttribute('show_in_list')) {
                    $targetSections[$startSection->getUid()][$childSection->getUid()] = $childSection;
                }

                // Add (recursivly) all sub sections that can take articles
                $tmpSections = $childSection->getChildSections(true);
                foreach ($tmpSections as $tmpSection) {
                    // Check if section can take articles.
                    if ($tmpSection->getAttribute('show_in_list')) {
                        $targetSections[$startSection->getUid()][$tmpSection->getUid()] = $tmpSection;
                    }
                }

                // If no sub section could be found for a start section, remove start section (if main section is allowed to take articles, the sub section IS NOT empty)
                if (sizeof($targetSections[$startSection->getUid()]) == 0) {
                    unset($targetSections[$key]); // no sub section for this base section, so do not list this base section
                }

            }
        }
        return $targetSections;
    }

    /**
     * Extract start section from base section using TSConfig setting in newspaper.baseSectionsAsStartSection
     * The return value might contain start section that does not have children.
     * @param array $baseSections
     * @return array sections
     */
    private function getStartSections(array $baseSections) {

        // Read User TSConfig for base sections (if available)
        $baseAsStartSectionUids = array();
        if ($GLOBALS['BE_USER']) {
            if ($GLOBALS['BE_USER']->getTSConfigVal('newspaper.baseSectionsAsStartSection')) {
                $baseAsStartSectionUids = t3lib_div::trimExplode(',', $GLOBALS['BE_USER']->getTSConfigVal('newspaper.baseSectionsAsStartSection'));
            }
        }

        $startSections = array();
        /** @var tx_newspaper_Section $baseSection */
        foreach($baseSections as $baseSection) {
            if (!in_array($baseSection->getUid(), $baseAsStartSectionUids)) {
                foreach($baseSection->getChildSections(false) as $startSection) {
                    $startSections[] = $startSection;
                }
            } else {
                $startSections[] = $baseSection;
            }
        }
        return $startSections;
    }


    /**
     * @return array Localized warnings and error messages for new article wozard
     */
    private function getNewArticleMessages() {
        global $LANG;
        return array(
            'no_section' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:message_no_section', false),
            'no_articletype' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:message_no_articletype', false),
            'no_section_chosen' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:message_no_section_chosen', false),
            'no_article_chosen' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:message_no_article_chosen', false),
            'no_ctrltagtype_available' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:message_no_ctrltagtype_available', false),
        );
    }

    /**
     * @return array Localized labels for new article wizard
     */
    private function getNewArticleLabels()     {
        global $LANG;
        return array(
            'new_article' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_new_article', false),
            'new_article_button' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_new_article_button', false),
            'new_article_typo3' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_new_article_typo3', false),
            'section' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_section', false),
            'section_base' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_section_base', false),
            'section_select' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_section_select', false),
            'articletype' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_articletype', false),
            'controltag' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_controltype', false),
            'to_productionlist' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_to_productionlist', false),
            'error_browsing' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_error_browsing', false),
            'no_sect' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_error_browsing', false),
        );
    }

    /// gets the default source for importing articles
	// \return name of source configured in TSConfig (newspaper.article.defaultSource), or "new" if not set
	private function getDefaultSource() {
		$tsc = t3lib_BEfunc::getPagesTSconfig(tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_article()));
//t3lib_div::devlog('getDefaultSource()', 'newspaper', 0, array('tsc' => $tsc['newspaper.']));
		if (!isset($tsc['newspaper.']['article.']['defaultSource'])) {
			return 'new'; // default source: just a plain new article
		}
		return $tsc['newspaper.']['article.']['defaultSource'];
	}

	/// creates a new article
	private function createNewArticle($input) {
//t3lib_div::devlog('createNewArticle()', 'newspaper', 0, array('input' => $input));
		/// just a plain typo3 article
		$s = new tx_newspaper_Section($input['section']);
		$at = new tx_newspaper_ArticleType($input['articletype']);

		$new_article = $s->createNewArticle($at);
//t3lib_div::devlog('at tsc musthave', 'newspaper', 0, $at->getTSConfigSettings('musthave'));
//t3lib_div::devlog('at tsc shouldhave', 'newspaper', 0, $at->getTSConfigSettings('shouldhave'));
		$new_article->setAttribute('articletype_id', $input['articletype']);

		// attach control tag, if any
		if ($input['controltag'] && $tag = new tx_newspaper_tag(intval($input['controltag']))) {
			$new_article->attachTag($tag);
		}

		// add creation date and user
		$new_article->setAttribute('crdate', time());
		$new_article->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
		$new_article->setAttribute('hidden', 1); // hide new article

		if ($input['controller'] == 'new_article_create_dummy') {
			// add some dummy content
			$new_article->setAttribute('kicker', 'Kicker ' . uniqid());
			$new_article->setAttribute('title', 'Title ' . uniqid());
			$new_article->setAttribute('teaser', tx_newspaper_be::getLoremIpsum());
			$new_article->setAttribute('bodytext', tx_newspaper_be::getLoremIpsum(rand(1, 3), true, false));
		}

		$new_article->storeWithoutSavehooks();

        $this->redirectToArticleMask($new_article, $input);
	}

	function browse_path(array $input) {

#t3lib_div::devlog('browse_path', 'mod5', 0, $input);
		$source_id = $input['source_id'];
		$path = $input['path'];
		$source = tx_newspaper::getRegisteredSource($source_id);

		$menu = $this->makeBrowseMenu($source_id, $path, $source);

		die($menu);
	}

	private function makeBrowseMenu($source_id, $path, tx_newspaper_Source $source) {

		$width = (intval($GLOBALS['BE_USER']->getTSConfigVal('tx_newspaper.article_source.browser_width')) > 0)? intval($GLOBALS['BE_USER']->getTSConfigVal('tx_newspaper.article_source.browser_width')) : 430; // 430px is default

        $ret = '<select name="' . $this->prefixId . 'source_path" size="10" style="width: ' . $width . 'px; float: left; margin-right: 16px; height: 400px;">' . "\n";

        $ret .= $this->makeMenuHeader($source_id, $path);

        foreach ($source->browse(new tx_newspaper_SourcePath($path)) as $entry) {
            $ret .= $this->makeMenuEntry($source_id, $source, $entry);
        }
        $ret .= '</select>' . "<br />\n";

        return $ret;
	}

	private function makeMenuHeader($source_id, $path) {

		global $LANG;

        $ret = '<option onclick="changeSource(\'' . $source_id . '\',\'\')"' . '>Top</option>' . "<br />\n";
        $ret .= '<option onclick="changeSource(\'' . $source_id . '\',\'' . $path . '\')"' . '>' .
                ($path? $LANG->getLL('label_reload'): '') . ' ' .
                $path . '</option>' . "<br />\n";

        return $ret;
	}

	private function makeMenuEntry($source_id, tx_newspaper_Source $source, tx_newspaper_SourcePath $entry) {
        if ($entry->isText()) {
            return $this->makeArticleMenuEntry($source_id, $source, $entry);
        } else {
            return $this->makeFolderMenuEntry($source_id, $entry);
        }
	}

	private function makeArticleMenuEntry($source_id, tx_newspaper_Source $source, tx_newspaper_SourcePath $entry) {
        return '<option title="' . ($entry->getTitle()) .
                     '" onclick="loadArticle(\'' . $source_id . '\',\'' . $entry->getID() .'\')"' . '>' .
                    ($entry->getTitle()) .
                    ' [' . $source->getProductionStatus($entry) . ']' .
                '</option>' . "\n";
	}

	private function makeFolderMenuEntry($source_id, tx_newspaper_SourcePath $entry) {
		return '<option title="' . ($entry->getTitle()) .
                     '" onclick="changeSource(\'' . $source_id . '\',\'' . $entry->getID() .'\')"' . '>' .
                   ($entry->getTitle()) .
               '</option>' . "\n";
	}

	function load_article() {
		$input = t3lib_div::GParrayMerged('tx_newspaper_mod5');
		$source_id = $input['source_id'];
		$path = $input['path'];
		$source = tx_newspaper::getRegisteredSource($source_id);

		$article = new tx_newspaper_Article();
		$source->readFields($article,
							array('title', 'teaser', 'bodytext'),
							new tx_newspaper_SourcePath($path));

		$import_info = '<input type="hidden" name="' . $this->prefixId . 'source_id" value="' . $source_id . '" />' .
					   '<input type="hidden" name="' . $this->prefixId . 'source_path" value="' . $path . '" />';

		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod5/res/preview/'));
		$smarty->assign('article', $article);
		$smarty->assign('source_id', $source_id);
		$smarty->assign('source_path', $path);

		$result = $smarty->fetch('mod5_articlepreview.tmpl');

		die($result);
	}

    function import_article(array $input) {

		$section = new tx_newspaper_Section(intval($input['section']));
		$articletype = new tx_newspaper_ArticleType(intval($input['articletype']));

        $source = tx_newspaper::getRegisteredSource($input['source_id']);
		$path = new tx_newspaper_SourcePath($input['source_path']);

		$tag = $input['controltag']? new tx_newspaper_tag(intval($input['controltag'])) : null;

		$new_article = $this->createAndImportArticle($articletype, $section, $source, $path, $tag);

		$this->logImport($new_article, $input);

		$this->redirectToArticleMask($new_article, $input);
    }

	/// Create an article of requested type, perform the import, set necessary attributes and store the article
	/** This function violates the "do one thing" rule clearly... anyway, still
	 *  better than leaving everything in import_article().
	 *
	 * @param $type    the selected article type.
	 * @param $section section the article belogs to - needed for the default extras.
	 * @param $source  source the article is imported from.
	 * @param $path
	 * @param $tag
	 */
    private function createAndImportArticle(tx_newspaper_ArticleType $type,
                                            tx_newspaper_Section $section,
                                            tx_newspaper_Source $source,
                                            tx_newspaper_SourcePath $path,
                                            tx_newspaper_tag $tag=null) {

        $new_article = $section->createNewArticle($type);
        $new_article->setAttribute('articletype_id', $type->getUid());

        $source->readArticle($new_article, $path);

        // atttach tag, if any
        if ($tag) {
        	$new_article->attachTag($tag);
        }

        // add creation date and user
        $new_article->setAttribute('crdate', time());
        $new_article->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
        $new_article->setAttribute('hidden', 1); // hide imported article

        $new_article->storeWithoutSavehooks();

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
    private function redirectToArticleMask(tx_newspaper_Article $new_article, array $input = array()) {

        /* @todo: Does this work with all browsers?
         * Is the following comment still true?
         * "Volle URL muss angegeben werden, weil manche Browser sonst 'http://' davorhaengen"
        */

        $base_url = tx_newspaper::getAbsolutePath();

        // add calling module to url in order to return to the correct calling module ...
        $url = $base_url . 'typo3/alt_doc.php?returnUrl=' . $base_url .
            'typo3conf/ext/newspaper/mod5/res/returnUrl.php?' . $this->extractCallingModuleAndFilter($input) . '&tx_newspaper_mod5[mod2Filter]=' . $input['mod2Filter'] . '&edit[tx_newspaper_article][' .
            $new_article->getUid() . ']=edit';

        header('Location: ' . $url); // redirect to article backend
    }

	private function changeRole(array $input) {
//t3lib_div::devlog('changeRole()', 'newspaper', 0, array('input' => $input));
		tx_newspaper_workflow::changeRole(intval($input['new_role']));
	}

	private function extractCallingModuleAndFilter(array $input=array()) {
		$url = 'tx_newspaper_mod5%5Bcalling_module%5D=';
		$url .= (intval($input['calling_module']))? intval($input['calling_module']) : 5; // 5 (= this module) is default
		$url .= '&tx_newspaper_mod5%5Bmod2Filter%5D=' . $input['mod2Filter'];
		return rawurlencode($url);
	}


	private function getControlTagsForCtrlTagType(array $input) {
//t3lib_div::devlog('tag', 'newspaper', 0, array('input' => $input));
		// ctrl tag category was changed -> read tags for new cat
		$tags = tx_newspaper_tag::getAllControlTags(intval($input['uid']));
		$option = array();
		foreach($tags as $tag) {
			$option[] = $tag->getUid() . '|' . htmlspecialchars($tag->getAttribute('tag'));
		}
		die(json_encode($option));
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
