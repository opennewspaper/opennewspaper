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
t3lib_div::devlog('moduleContent()', 'newspaper', 0, array('setting' => $this->MOD_SETTINGS['function'], 'input' => $input));
		
		// check for AJAX request first
		$this->processAjax($input); // executin will die, if an Ajax request was processed
		
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 'manage_dossiers':
				$this->manageDossiers($input);
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
	
	
	/// Check if an AJAX reuqest is to be processed. If yes, generate response and die.
	private function processAjax(array $input) {

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

		if (isset($input['AjaxTag'])) {
			// tag was changed -> render tag zone backend
			die($this->renderTagZoneBackend(intval($input['AjaxTag'])));
		}


	}
	
	/// Render tag zone backend for "Manage dossiers" module
	private function renderTagZoneBackend($uid) {
		$tag = new tx_newspaper_tag(intval($uid));
		$tagzones = $tag->getTagzones();
		$tz_extras = array();
		foreach($tagzones as $row) {
t3lib_div::devlog('tz', 'newspaper', 0, $row);
			$tz_extras[$row['tag_zone']] = $tag->getTagzoneExtras($row['tag_zone']);
		}
		$this->smarty->assign('TAG', $tag);
		$this->smarty->assign('TAGZONES_USED', $tagzones);
		$this->smarty->assign('TAGZONES_USED_EXTRAS', $tz_extras);
		$this->smarty->assign('TAGZONES_ALL', tx_newspaper_tag::getAllTagzones());
		$this->smarty->assign('ICON', array(
			'remove' => tx_newspaper_be::renderIcon('gfx/clearout.gif', ''),
			'replace' => tx_newspaper_be::renderIcon('gfx/import_update.gif', ''),
			'add' => tx_newspaper_be::renderIcon('gfx/plusbullet2.gif', '')
		));
t3lib_div::devlog('renderTagZoneBackend()', 'newspaper', 0, array('tag' => $tag, "tz's" => $tag->getTagzones(), "all tz's" => tx_newspaper_tag::getAllTagzones(), 'tz e\'s' => $tz_extras));
		return $this->smarty->fetch('mod6_dossier_tagzone.tmpl');
	}
	
	
	// backend for new dossier wizard
	private function createDossier($input) {
//t3lib_div::devlog('createDossier()', 'newspaper', 0, array('input' => $input));

		$cats = tx_newspaper_tag::getAllControltagCategories();

		$submitted = (bool) (count($input)); // check if form was submitted ...
		$error = array(
			'tagNotUnique' => $this->checkTagNotUnique($input['tag'], $input['ctrltagcat']), // true, if tag is not unique
			'tagEmpty' => ($input['tag'] == ''),  // tag name is mandatory
			'titleMissing' => ($input['title'] == ''), // title is mandatory 
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



	// backend for managing dossiers
	private function manageDossiers($input) {
t3lib_div::devlog('manageDossiers()', 'newspaper', 0, array('input' => $input));

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
		}
		
		
		// render backend
		$this->content .= $this->doc->section(
			$this->localLang['label_manage_dossier'], 
			$this->smarty->fetch('mod6_dossier_manage.tmpl'),
			0, 
			1
		);
		
t3lib_div::devlog('manageDossiers()', 'newspaper', 0, array('tagCats' => $tagCats, 'submitted' => $submitted));
		
	}

	
//	// backend for managing dossiers
//	private function manageDossiersOLD($input) {
//t3lib_div::devlog('manageDossiers()', 'newspaper', 0, array('input' => $input));
//		$this->handlePOST();
//		
//		$this->smarty->assign('tag_zones', self::getAvailableTagZones());
//		$this->smarty->assign('tags', self::getAvailableTags());
////t3lib_div::devlog('mod6', 'newspaper', 0, array('tags' => self::getAvailableTags()));
//		$this->smarty->assign('extra_types', self::getAvailableExtraTypes());				
//				
//		$data = tx_newspaper::selectRows(
//			'*', self::controltag_to_extra_table, '', '', 'uid DESC'
//		);
//				
//		if ($data) {
//			foreach ($data as $index => $row) {
//				$tag = tx_newspaper::selectOneRow(
//					'tag', 'tx_newspaper_tag', 'uid = ' . $row['tag']
//				);
//				$data[$index]['tag'] = $tag['tag'];
//				$tag_zone = tx_newspaper::selectOneRow(
//					'name', 'tx_newspaper_tag_zone', 'uid = ' . $row['tag_zone']
//				);
//				$data[$index]['tag_zone'] = $tag_zone['name'];
//						
//				try {
//					$extra = new $row['extra_table']($row['extra_uid']);
//					$data[$index]['extra_uid'] = $extra->getDescription();
//				} catch (tx_newspaper_EmptyResultException $e) {
//					$data[$index]['extra_uid'] = 
//						'<input name="extra_uid[' . $data[$index]['uid'] . ']" />';
//				}
//			}
//			$this->smarty->assign('data', $data);
//							
//		}
//		$this->content .= $this->doc->section(
//			'Message #1:', 
//			$this->smarty->fetch('mod6.tmpl'),
//			0, 1
//		);
//	}
//	
//	
//	////////////////////////////////////////////////////////////////////////////
//	
//	/// Handle user input
//	/** Creates a new tag zone/tag/extra combination if the user entered one.
//	 */
//	private function handlePOST() {
//		if ($_POST) {
//			//	reorder $_POST so it is arranged as an array (
//			//		UIDs => array (field names => field values))
//			$data_by_uid = array();
//			foreach ($_POST as $field => $rows) {
//				if (in_array($field, self::$writable_fields)) {
//					foreach ($rows as $uid => $row) {
//						if (!$data_by_uid[$uid]) $data_by_uid[$uid] = array();
//						$data_by_uid[$uid][$field] = $row;
//					}
//				}
//			}
//			foreach ($data_by_uid as $uid => $values) {
//       			if ($uid == 0) {
//	       			// insert the shit if uid == 0 and an Extra is selected
//	       			if (intval($values['extra_uid']))
//						tx_newspaper::insertRows(self::controltag_to_extra_table, $values);
//       			} else {
//		   			// update otherwise
//					tx_newspaper::updateRows(self::controltag_to_extra_table, 'uid = ' . $uid, $values);
//       			}
//   				$this->content .= '<p>' . tx_newspaper::$query . '</p>';
//			}
//		}		
//	}
//	
//	///	Returns all tag zones
//	static private function getAvailableTagZones() {
//		return tx_newspaper::selectRows(
//			'uid, name', 
//			self::tag_zone_table,
//			'1' . tx_newspaper::enableFields(self::tag_zone_table)
//		);
//	}

//	///	Returns all control tags
//	static private function getAvailableTags() {
//		return tx_newspaper::selectRows(
//			'uid, tag', self::tag_table,
//			'tag_type = ' . tx_newspaper_tag::getControlTagType() 
//		);
//	}

//	///	Returns all classes which have registered as an tx_newspaper_Extra
//	static private function getAvailableExtraTypes() {
//		$extra_types = array();
//		foreach (tx_newspaper_Extra::getRegisteredExtras() as $registered_extra) {
//			$extra_types[] = array(
//				'table' => $registered_extra->getTable(), 
//				'title' =>$registered_extra->getTitle()
//			);
//		}
//		return $extra_types;
//	}
	
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