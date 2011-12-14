<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
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


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:newspaper/mod6/locallang.xml');

// read additional xml files (hook)
if ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/newspaper']['mod4']['additionalLocallang']) {
	foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/newspaper']['mod4']['additionalLocallang'] as $file) {
		$LANG->includeLLFile($file);
	}
}


require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

/// Class to generate a BE module with 100% width
class fullWidthDoc_mod6 extends template {
	var $divClass = 'typo3-fullWidthDoc';	///< Sets width to 100%
}


/// Module 'Control Tag Control' for the 'newspaper' extension.
/** A BE for assigning shown tx_newspaper_Extra s to a combination of
 *  tx_newspaper_Tag and tx_newspaper_TagZone. By placing a
 *  tx_newspaper_Extra_ControlTagZone Extra and assigning it a tag zone, a
 *  dossier can be supplied with Extras shown only with tx_newspaper_Article s
 *  which are tagged with a specific tx_newspaper_Tag.
 *
 *  The central function which handles all the action is moduleContent().
 *
 * \author	Helge Preuss <helge.preuss@gmail.com>
 */
class  tx_newspaper_module6 extends t3lib_SCbase {

	private $prefixId = 'tx_newspaper_mod6';
	private $localLang = null; // localization stuff

	var $pageinfo;

	private $smarty = null;				///< tx_newspaper_Smarty rendering engine



	///	Table mapping tx_newspaper_Extra s to tag zones and tx_newspaper_Tag s
	const controltag_to_extra_table = 'tx_newspaper_controltag_to_extra';

//	///	Table listing the possible tag zones
//	const tag_zone_table = 'tx_newspaper_tag_zone';

//	///	Table listing the possible tags
//	const tag_table = 'tx_newspaper_tag';

	///	Fields in the controltag_to_extra_table not to display (system fields)
	private static $excluded_fields = array(
		'uid', 'pid', 'tstamp', 'crdate', 'cruser_id'
	);

	private static $writable_fields = array(
		'tag', 'tag_zone', 'extra_table', 'extra_uid'
	);


	/// Initializes the Module
	/** Initializes a tx_newspaper_Smarty instance and a \c language object for
	 *  internationalization of BE messages.
	 *
	 * \return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// get ll labels
		$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod6/locallang.xml', $GLOBALS['LANG']->lang);
		$this->localLang = $localLang[$GLOBALS['LANG']->lang];

		// read additional locallang files (hook)
		if ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/newspaper']['mod4']['additionalLocallang']) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/newspaper']['mod4']['additionalLocallang'] as $file) {
				$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper_taz/locallang_newspaper_taz.xml', $GLOBALS['LANG']->lang);
				$this->localLang = array_merge($this->localLang, $localLang[$GLOBALS['LANG']->lang]);
			}
		}

		$this->smarty = new tx_newspaper_Smarty();
		$this->smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod6/res/'));
		$this->smarty->assign('LANG', $this->localLang);

		if (!($LANG instanceof language)) {
			require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
			$LANG = t3lib_div::makeInstance('language');
			$LANG->init('default');
		}

		parent::init();

	}

	/// Adds items to the ->MOD_MENU array. Used for the function menu selector.
	/** \todo Make a real menu. Options: manage dossiers, create new tags.
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = array(
			'function' => array(
				'manage_dossiers' => $LANG->getLL('manage_dossiers'),
//				'manage_tagzones' => $LANG->getLL('manage_tagzones'),
				'wizard_dossier' => $LANG->getLL('wizard_dossier'),
//              'manage_articles' => $LANG->getLL('manage_articles'), // \todo: this wizard still needed? moved to 'manage dossiers'
			)
		);
		parent::menuConfig();
	}

	/// Main function of the module. Writes the content to \c $this->content.
	/** If you chose "web" as main module, you will need to consider the
	 *  \c $this->id parameter which will contain the uid-number of the page
	 *  clicked in the page tree.
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		$access = $BE_USER->user['uid']? true : false; // \todo: better check needed

		if ($access) {

			// Draw the header.
			$this->doc = t3lib_div::makeInstance('fullWidthDoc_mod6');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

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

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->section('', $this->doc->funcMenu('', t3lib_BEfunc::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function'])));
			$this->content .= $this->doc->divider(5);

			// Render content:
			$this->moduleContent();

			// ShortCut
//			if ($BE_USER->mayMakeShortcut())	{
//				$this->content .= $this->doc->spacer(20) . $this->doc->section('', $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));
//			}

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

	/// Prints out the module HTML
	function printContent()	{
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/// Generates the module content, depending on the menu values chosen.
	/** Prints a list of present tag zone/tag/extra combinations and enables the
	 *  user to select each of those.
	 *
	 *  \todo Create new tags if the function menu is thus selected
	 *  \todo paging of entries
	 *  \todo don't create a new combination on every POST
	 */
	function moduleContent()	{
		global $LANG;

		$input = t3lib_div::GParrayMerged($this->prefixId);
//t3lib_div::devlog('moduleContent()', 'newspaper', 0, array('setting' => $this->MOD_SETTINGS['function'], 'input' => $input));

		// check for AJAX request first
		$this->processAjax($input); // execution will die, if an Ajax request was processed

		switch((string)$this->MOD_SETTINGS['function'])	{
			case 'manage_dossiers':
				$this->manageDossiers($input);
			break;
            case 'manage_articles':
                $this->manageArticles($input);
            break;
//			case 'manage_tagzones':
//				$this->manageTagzones($input);
//			break;
			case 'wizard_dossier':
				$this->createDossier($input);
			break;
			default:
				t3lib_div::devlog('mod6 - Unknown function chosen', 'newspaper', 2);
		}


	}


	/// Check if an AJAX request is to be processed. If yes, generate response and die.
	private function processAjax(array $input) {

		// newspaper element browser
		if (isset($input['ajaxController'])) {
			// add etxra to tagzone
			switch($input['ajaxController']) {
				case 'manageDossiers':
					// create new abstract record for given concrete etxra
					$abstractUid = tx_newspaper_extra::createExtraRecord(intval($input['uid']), htmlspecialchars($input['className']), true);
					$extra = tx_newspaper_Extra_Factory::getInstance()->create($abstractUid);
					// add this extra to tag zone and tag
					$tag = new tx_newspaper_tag(intval($input['tag_uid']));
//t3lib_div::devlog('processAjax()', 'newspaper', 0, array('abst uid' => $abstractUid, 'e' => $extra, 'tag_uid' => $input['tag_uid'], 'tag' => $tag));
					$tag->addExtraToTagZone($extra, intval($input['tz_uid']));
					die();
				default:
					t3lib_div::devlog('Unknown AJAX controller in mod6', 'newspaper', 3, array('input' => $input));
			}
		}


		// attach tag to selected articles
		if (isset($input['AjaxBatchAttachTag']) && intval($input['AjaxBatchAttachTag'])) {
			// batch assign tag to articles
			$msg = '';
			$count = 0;
			foreach(t3lib_div::trimExplode(',', $input['articleUids']) as $aUid) {
				$aUid = intval($aUid);
				if ($aUid) {
					$a = new tx_newspaper_Article($aUid);
					try {
						$a->getAttribute('uid'); // access article in order to check article existence
						$t = new tx_newspaper_tag(intval($input['AjaxBatchAttachTag']));
						if ($a->attachTag($t)) {
							$msg .= $a->getAttribute('kicker') . ': ' . $a->getAttribute('title') . ' (#' . $a->getUid() . ')<br />';
							$count++;
						}
					} catch(tx_newspaper_EmptyResultException $e) {
						// ignore deleted articles
					}
				}
			}
			if ($count == 0) {
				$msg = $this->localLang['label_attach_tag_0'];
			} elseif ($count == 1) {
				$msg = $this->localLang['label_attach_tag_1'] . '<br />' . $msg;
			} else {
				$msg = str_replace('###COUNT###', $count, $this->localLang['label_attach_tag_2plus']) . '<br />' . $msg;
			}
			die($msg);
		}


		// detach tag from selected articles
		if (isset($input['AjaxBatchDetachTag']) && intval($input['AjaxBatchDetachTag'])) {
			// batch assign tag to articles
			$msg = '';
			$count = 0;
			foreach(t3lib_div::trimExplode(',', $input['articleUids']) as $aUid) {
				$aUid = intval($aUid);
				if ($aUid) {
					$a = new tx_newspaper_Article($aUid);
					try {
						$t = new tx_newspaper_tag(intval($input['AjaxBatchDetachTag']));
						if ($a->detachTag($t)) {
							$msg .= $a->getAttribute('kicker') . ': ' . $a->getAttribute('title') . ' (#' . $a->getUid() . ')<br />';
							$count++;
						}
					} catch(tx_newspaper_EmptyResultException $e) {
						// ignore deleted articles
					}
				}
			}
			if ($count == 0) {
				$msg = $this->localLang['label_detach_tag_0'];
			} elseif ($count == 1) {
				$msg = $this->localLang['label_detach_tag_1'] . '<br />' . $msg;
			} else {
				$msg = str_replace('###COUNT###', $count, $this->localLang['label_detach_tag_2plus']) . '<br />' . $msg;
			}
			die($msg);
		}


		// list articles using tag
		if (isset($input['AjaxListArticlesForCtrlTag'])) {
			$msg = '';

			if ($tag = new tx_newspaper_tag(intval($input['AjaxListArticlesForCtrlTag']))) {
				$count = 0;
				foreach($tag->getArticles() as $article) {
					$count++;
					$msg .= '<input type="checkbox" class="detachTag" id="detach_' . $article->getUid() . '" /> ';
					$msg .= $count . '. ' . $article->getAttribute('kicker') . ': ' . $article->getAttribute('title') . ' (#' . $article->getUid() . ')<br />';
				}
			}

			if (!$msg) {
				$msg = '---'; // \todo a real (and localized) message wouldn't hurt ...
			}

			die($msg);
		}


		// read tags for control tag category
		if (isset($input['AjaxCtrlTagCat'])) {
			// ctrl tag category was changed -> read tags for new cat
			$tags = tx_newspaper_tag::getAllControlTags(intval($input['AjaxCtrlTagCat']));
			$option = array();
			$option[0] = '0|'; // start with empty option
			foreach($tags as $tag) {
				$option[] = $tag->getUid() . '|' . htmlspecialchars($tag->getAttribute('tag'));
			}
			die(json_encode($option));
		}


		// render tagzones for selected tag
		if (isset($input['AjaxTag'])) {
			// tag was changed -> render tag zone backend
			die($this->renderTagZoneBackend(intval($input['AjaxTag'])));
		}


		// remove extra from tagzone
		if (isset($input['AjaxRemoveExtraFromTagZone'])) {
// \todo: move to extra class?
			$tag = new tx_newspaper_tag(intval($input['tag_uid']));
			// set deleted flag for this tag zone, control tag and extra uid combination
			tx_newspaper::deleteRows(
				'tx_newspaper_controltag_to_extra',
				intval($input['tz_uid']),
				'tag_zone',
				'tag=' . $tag->getUid() . ' AND extra=' . intval($input['e_uid'])
			);

			// generate dossier
			$tree = tx_newspaper_DependencyTree::generateFromTag($tag);
			$tree->executeActionsOnPages('exportTags');

			die($this->renderTagZoneBackend(intval($input['tag_uid']))); // render new tag zone backend
		}


		// change dossier title
		if (isset($input['AjaxStoreDossierTitleUid'])) {

			$title = trim($input['dossierTitle']);

			$tag = new tx_newspaper_tag(intval($input['AjaxStoreDossierTitleUid']));

			if (!$tag->isTitleUnique($title)) {
				die(json_encode(array('success' => false)));
			}

			$tag->setAttribute('title', $title);
			$tag->store();
			die(json_encode(array('success' => true)));
		}


		// change section
		if (isset($input['AjaxStoreDossierSectionUid'])) {
			$tag = new tx_newspaper_tag(intval($input['tagUid']));
			$tag->setAttribute('section', intval($input['AjaxStoreDossierSectionUid'])); // store new section
			$tag->store();
		}

	}

	/// Render tag zone backend for "Manage dossiers" module
	private function renderTagZoneBackend($uid) {
		$tag = new tx_newspaper_tag(intval($uid));
		$tagzones = $tag->getTagzones();
		$tz_extras = array();
		foreach($tagzones as $row) {
//t3lib_div::devlog('tz', 'newspaper', 0, $row);
			$tz_extras[$row['tag_zone']] = $tag->getTagzoneExtras($row['tag_zone']);
		}

		$this->smarty->assign('TSCONFIG', $this->getTagzoneConfig()); // TSConfig settings
		$this->smarty->assign('TAG', $tag);
		$this->smarty->assign('TAGZONES_USED', $tagzones);
		$this->smarty->assign('TAGZONES_USED_EXTRAS', $tz_extras);
		$this->smarty->assign('TAGZONES_ALL', tx_newspaper_tag::getAllTagzones());
		$this->smarty->assign('ABSOLUTE_PATH', tx_newspaper::getAbsolutePath());
		$this->smarty->assign('ICON', array(
			'remove' => tx_newspaper_be::renderIcon('gfx/clearout.gif', ''),
			'replace' => tx_newspaper_be::renderIcon('gfx/import_update.gif', ''),
			'add' => tx_newspaper_be::renderIcon('gfx/plusbullet2.gif', ''),
			'articlebrowser' => tx_newspaper_BE::renderIcon('gfx/insert3.gif', ''),
			'x' => tx_newspaper_BE::renderIcon('gfx/close.gif', ''),
			'edit' => tx_newspaper_BE::renderIcon('gfx/edit2.gif', ''),
			'reload' => tx_newspaper_BE::renderIcon('gfx/refresh_n.gif', '')
		));
//t3lib_div::devlog('renderTagZoneBackend()', 'newspaper', 0, array('tag' => $tag, "tz's" => $tag->getTagzones(), "all tz's" => tx_newspaper_tag::getAllTagzones(), 'tz e\'s' => $tz_extras));

		$s = $tag->getSection();

		return json_encode(array(
			'backend' => $this->smarty->fetch('mod6_dossier_tagzone.tmpl'),
			'dossierTitle' => $tag->getAttribute('title'),
			'sectionTitle' => $s->getAttribute('section_name'),
			'sectionUid' => $s->getUid()
		));
	}


	// backend for new dossier wizard
	private function createDossier($input) {
//t3lib_div::devlog('createDossier()', 'newspaper', 0, array('input' => $input));

		$cats = tx_newspaper_tag::getAllControltagCategories();

		$tag = new tx_newspaper_tag();// empty tag

		$submitted = (bool) (count($input)); // check if form was submitted ...
		$error = array(
			'tagNotUnique' => $this->checkTagNotUnique($input['tag'], $input['ctrltagcat']), // true, if tag is not unique
			'tagEmpty' => ($input['tag'] == ''),  // tag name is mandatory
			'titleMissing' => ($input['title'] == ''), // title is mandatory
			'titleNotUnique' => !$tag->isTitleUnique($input['title']), // title must be unique
			'sectionMissing' => ($input['section'] == 0), // associated section is mandatory
			'noCtrlTagCatAvailable' => (sizeof($cats) == 0) // dossier wizard can't be used without control tag categories
		);
		$this->smarty->assign('ERROR', $error);

		if ($submitted && !$this->containsError($error)) {
			// valid data submitted ... so store data
			$tag = tx_newspaper_tag::createControlTag($input['ctrltagcat'], $input['tag'], $input['title'], $input['section']);
			$tag->store();
			$this->smarty->assign('wizardDossierContinue', true); // set flag that wizard created dossier successfully
		} else {
			// assign data to smarty
			$this->smarty->assign('DATA',
				array(
					'tag' => (isset($input['tag']))? $input['tag'] : '',
					'ctrltagcat' => intval($input['ctrltagcat']), // always set (no empty option in select box)
					'title' => (isset($input['title']))? $input['title'] : '',
					'section' => (isset($input['section']))? $input['section'] : 0,
					'submitted' => $submitted, // true if form was submitted, false if form was just opened
				)
			);
			$this->smarty->assign('SECTIONS', tx_newspaper_section::getAllSections(false));
			$this->smarty->assign('CTRLTAGCATS', $cats);
		}

		// render backend
		$this->content .= $this->doc->section(
			$this->localLang['label_create_dossier'],
			$this->smarty->fetch('mod6_dossier_wizard.tmpl'),
			0,
			1
		);

	}

	// \return true, if at least one item in array $error is true, else false
	private function containsError(array $error) {
		foreach($error as $item) {
			if ($item) {
				return true;
			}
		}
		return false;
	}

	/// \return false, if tag is unique (for control tag category) or empty, else true; tag is ALWAYS a control tag
	private function checkTagNotUnique($tag, $ctrltagcat) {
		if (!$tag) {
			return false;
		}
		return tx_newspaper_tag::doesControlTagAlreadyExist($tag, $ctrltagcat);
	}



	// backend for managing tag zones
	private function manageTagzones($input) {
t3lib_div::devlog('manageTagzones() - not implemented yet', 'newspaper', 0, array('input' => $input));
	}

    // backend for managing tag zones
    private function manageArticles($input) {
t3lib_div::devlog('manageArticles() - not implemented yet', 'newspaper', 0, array('input' => $input));
    }

	// backend for managing dossiers
	private function manageDossiers($input) {
//t3lib_div::devlog('manageDossiers()', 'newspaper', 0, array('input' => $input));
		// get control tag categories
		$tagCats = tx_newspaper_tag::getAllControltagCategories();

		$submitted = (bool) (count($input)); // check if form was submitted ...

		$error = array(
			'noCtrlTagCatAvailable' => (sizeof($tagCats) == 0) // control tag catgeories are mandatory
		);
		$this->smarty->assign('ERROR', $error);

		// get chosen ctrl tag cat (or use first cat as default)
		$ctrltagcat = (isset($input['ctrltagcat']))?
			intval($input['ctrltagcat']) :
			(isset($tagCats[0]['uid']))? intval($tagCats[0]['uid']): 0;

		if ($ctrltagcat > 0) {
			// assign data to smarty
			$this->smarty->assign('DATA',
				array(
					'submitted' => $submitted, // true if form was submitted, false if form was just opened
				)
			);
			$this->smarty->assign('CTRLTAGCATS', $tagCats);
			$this->smarty->assign('TAGS', tx_newspaper_tag::getAllControlTags($ctrltagcat));

			$this->smarty->assign('SECTIONS', tx_newspaper_section::getAllSections());

			$this->smarty->assign('ICON', array(
				'articlebrowser' => tx_newspaper_BE::renderIcon('gfx/insert3.gif', ''),
				'x' => tx_newspaper_BE::renderIcon('gfx/close.gif', ''),
				'edit' => tx_newspaper_BE::renderIcon('gfx/edit2.gif', ''),
				'save' => tx_newspaper_BE::renderIcon('gfx/saveandclosedok.gif', '')
			));
		}


		// render backend
		$this->content .= $this->doc->section(
			$this->localLang['label_manage_dossier'],
			$this->smarty->fetch('mod6_dossier_manage.tmpl'),
			0,
			1
		);

//t3lib_div::devlog('manageDossiers()', 'newspaper', 0, array('tagCats' => $tagCats, 'submitted' => $submitted));

	}

    /**
     * Get TSConfig configuration for tag zones
     *
     * TSConfig example:
     * newspaper.dossierWizard.mustHaveTagzones = 1|tx_newspaper_extra_images
     * newspaper.dossierWizard.shouldHaveTagzones = 2
     *
     * Musthave tagzones will be rendered with a red frame in the Dossier module backend
     * Shouldhave tagzones will be rendered with a yellow frame in the Dossier module backend
     *
     * Musthave configuration overrides Shouldhave configuration
     *
     * @return Array with tagzone configuration: array('mustHave' => array(uid1 => class|empty, ..., uidn => class|empty), 'shouldHave' => array(...))
     * @todo: move to tagzone class?
     */
	private function getTagzoneConfig() {
		require_once  PATH_typo3conf . '/ext/newspaper/tx_newspaper_include.php';

        $tsc = tx_newspaper::getTSConfig();

        $conf['mustHave'] = $tsc['newspaper.']['dossierWizard.']['mustHaveTagzones']?
            $tsc['newspaper.']['dossierWizard.']['mustHaveTagzones'] : array();
        $conf['shouldHave'] = $tsc['newspaper.']['dossierWizard.']['shouldHaveTagzones']?
            $tsc['newspaper.']['dossierWizard.']['shouldHaveTagzones'] : array();
//t3lib_div::debug($conf, 'conf');

        // Move TSConfig configuration into array
		$data = array();
        foreach($conf as $key => $config) {
            // Process for mustHave and shouldHave tagzones
            foreach(t3lib_div::trimExplode(',', $config) as $line) {
                $cmd = t3lib_div::trimExplode('|', $line);
                if (sizeof($cmd) > 1) {
                    // Add tagzone uid and assign the Extra class to be assigned
                    $data[$key][intval($cmd[0])] = $cmd[1];
                } else {
                    // Just add tagzone uid to configuration
                    $data[$key][intval($cmd[0])] = '';
                }
            }
        }

        // Remove shouldHave configuration for mustHave tagzone uid
        foreach($data['shouldHave'] as $key => $value) {
            if (array_key_exists($key, $data['mustHave'])) {
                unset($data['shouldHave'][$key]);
            }
        }

//t3lib_div::devlog('getTagzoneConfig()', 'newspaper', 0, array('data' => $data, 'conf' => $conf));

		return $data;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod6/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod6/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_newspaper_module6');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>