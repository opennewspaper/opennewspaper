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


/// \todo: @oliver: major clean up needed!


//unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH . 'init.php');
require_once($BACK_PATH . 'template.php');


/// Class to generate a BE module with 100% width
class fullWidthDoc_mod3 extends template {
	var $divClass = 'typo3-fullWidthDoc';	///< Sets width to 100%
}



$LANG->includeLLFile('EXT:newspaper/mod3/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]


/**
 * Module 'Placement' for the 'newspaper' extension.
 *
 * @author	Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 */
class  tx_newspaper_module3 extends t3lib_SCbase {
	var $pageinfo;

	private $prefixId = 'tx_newspaper_mod3';
	private $input = array(); // stores param data \todo: replace all params with prefixed params

	private $section_id;
	private $page_id;
	private $pagezone_id;

	private $page_zone_id;
	private $pagezone_type_id;

	private $show_levels_above;
	private $show_visible_only;


	/// functions call by ajax calls //////////////////////////////////////////

	private function processToggleShowLevelsAbove($checked) {
		global $BE_USER;
		if (strtolower($checked) == 'true') {
			$checked = true;
		} else {
			$checked = false;
		}
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/show_levels_above", $checked); // store status of checkbox for be_user
		die();
	}

	private function processToggleShowVisibleOnly($checked) {
		global $BE_USER;
		if (strtolower($checked) == 'true') {
			$checked = true;
		} else {
			$checked = false;
		}
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/show_visible_only", $checked); // store status of checkbox for be_user
		die();
	}


	private function processPageTypeChange($pt_uid) {
		global $BE_USER;
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/page_type_id", intval($pt_uid)); // store page type for be_user
		die();
	}

	private function processPageZoneTypeChange($pzt_uid) {
		global $BE_USER;
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/pagezone_type_id", intval($pzt_uid)); // store pagezone type for be_user
		die();
	}


	/// change inheritance source
	/** possible values:
	 *    0: default, get from same pagezone type from upper level
	 *   -1: none: do not inherit any extra
	 *  uid: uid of a concrete pagezone on the same level (pagezone_page); not available for articles (articles are unique to a section)
	 */
	private function processInheritanceSourceChange($pz_uid, $new_parent_pagezone_value) {
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid)); // create pagezone or article
		$pz->changeParent(intval($new_parent_pagezone_value));
        tx_newspaper_PageZone::updateDependencyTree($pz);
		die();
	}


	/// called via ajax: render list of extras for concrete article
	/** \param $article_uid uid of concrete(!) article */
	private function processReloadExtaInConcreteArticle($article_uid) {
		$a = new tx_newspaper_article($article_uid);
		if ($a->isConcreteArticle()) {
			echo tx_newspaper_be::renderBackendPageZone($a, false, true);
		}
		die();
	}



	/// called via ajax: insert extra on pagezone (if concrete article html code with list of extras is returned)
	/** \param $origin_uid origin UID of Extra after which to insert a new one
	 *  \param $pz_uid uid of pagezone (can be pagezone_page, default article or concrete article)
	 *  \param $paragraph If in an Article, paragraph in which to insert
	 *  \todo: check if obsolete
	 */
	private function processExtraInsertAfter($origin_uid, $pz_uid, $paragraph=false) {
t3lib_div::devlog('processExtraInsertAfter() obsolete???', 'newspaper', 0, array('origin_uid' => $origin_uid, 'pz_uid' => $pz_uid, 'paragraph' => $paragraph, 'backtrace' => debug_backtrace()));
//		$e = new tx_newspaper_Extra_Image();
//		$e->setAttribute('title', '');
//		$e->store();
//		$e->setAttribute('show_extra', 1);
//		$e->setAttribute('is_inheritable', 1);
//
//		$e->store();
//		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
//		$pz->insertExtraAfter($e, intval($origin_uid));
//
//		if ($pz->isConcreteArticle()) {
//			echo tx_newspaper_be::renderBackendPageZone($pz, false);
//		}
//
		die();
	}

	private function processExtraInsertAfterFromPoolCopy($origin_uid, $extra_class, $pooled_extra_uid, $pz_uid, $paragraph, $path) {
		$origin_uid = intval($origin_uid);
		$pooled_extra_uid = intval($pooled_extra_uid);
		$pz_uid = intval($pz_uid);
//		$paragraph = intval($paragraph); // \todo: needed???

		$pz = tx_newspaper_PageZone_Factory::getInstance()->create($pz_uid); // create pagezone or article

		$e = new $extra_class($pooled_extra_uid);
		$copied_extra = $e->duplicate();
		$copied_extra->setAttribute('pool', false); // otherwise a copied pooled extra would be a pooled extra too
		// set default value fpr show and pass down
		$copied_extra->setAttribute('show_extra', true);
		$copied_extra->setAttribute('is_inheritable', true);
		$copied_extra->store();

		$pz->insertExtraAfter($copied_extra, $origin_uid);

        tx_newspaper_PageZone::updateDependencyTree($pz);

		header('location: http://' . $_SERVER['SERVER_NAME'] . $path . 'typo3conf/ext/newspaper/mod3/res/close.html');
		die();
	}
	private function processExtraInsertAfterFromPoolReference($origin_uid, $extra_class, $pooled_extra_uid, $pz_uid, $paragraph, $path) {
		$origin_uid = intval($origin_uid);
		$pooled_extra_uid = intval($pooled_extra_uid);
		$pz_uid = intval($pz_uid);
//		$paragraph = intval($paragraph); // \todo: needed???

		$pz = tx_newspaper_PageZone_Factory::getInstance()->create($pz_uid); // create pagezone_page or article

		$abstract_uid = tx_newspaper_Extra::createExtraRecord($pooled_extra_uid, $extra_class, true); // true = force new record to be written

		$e = tx_newspaper_Extra_Factory::getInstance()->create($abstract_uid);
//		$e->setAttribute('origin_uid', $origin_uid); // store uid of referenced extra, so this extra can be identified as a referenced extra
/// \todo: show / passdown: get values from origin extra and use setAttr() or set default values for show and passdown?
//		$e->store();

/// \todo: check why SECOND extra records gets written
		$pz->insertExtraAfter($e, $origin_uid);

        tx_newspaper_PageZone::updateDependencyTree($pz);

//\todo: close.html/close_in_article.html
		header('location: http://' . $_SERVER['SERVER_NAME'] . $path . 'typo3conf/ext/newspaper/mod3/res/close.html');
		die();
	}


	private function processExtraInsertAfterDummy($origin_uid, $pz_uid) {
/// \todo: remove after testing
		$e = new tx_newspaper_Extra_Image();
		$e->setAttribute('title', 'Dummy ' . rand(1, 1000));
		$e->store();
		$e->setAttribute('show_extra', 1);
		$e->setAttribute('is_inheritable', 1);

		$e->store();
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
		$pz->insertExtraAfter($e, $origin_uid);

        tx_newspaper_PageZone::updateDependencyTree($pz);

		die();
	}

	/// called via ajax: move extra on pagezone (if concrete article html code with list of extras is returned)
	/** \param $origin_uid uid of extra AFTER that the extra to be moved is moved to
	 *  \param $pz_uid uid of pagezone (can be pagezone_page, default article or concrete article)
	 *  \param $extra_uid uid of extra
	 */
	private function processExtraMoveAfter($origin_uid, $pz_uid, $extra_uid) {
        tx_newspaper::devlog("processExtraMoveAfter($origin_uid, $pz_uid, $extra_uid)");
		$e = tx_newspaper_Extra_Factory::getInstance()->create(intval($extra_uid));
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
		$pz->moveExtraAfter($e, $origin_uid);

		if ($pz->isConcreteArticle()) {
			// \todo: Helge, if I don't re-read the pagezone, the new position for the moved extra is not correct (see #564)
			$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid)); // re-reading the pagezone ...
			echo tx_newspaper_be::renderBackendPageZone($pz, false, true);
		}

        tx_newspaper_PageZone::updateDependencyTree($pz);

		die();
	}

	/// called via ajax: delete extra on pagezone (if concrete article html code with list of extras is returned)
	/** \param $pz_uid uid of pagezone (can be pagezone_page, default article or concrete article)
	 *  \param $extra_uid uid of extra
	 */
	private function processExtraDelete($pz_uid, $extra_uid) {
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));

		$e = tx_newspaper_Extra_Factory::getInstance()->create(intval($extra_uid));
		$pz->removeExtra($e);

		if ($pz->isConcreteArticle()) {
			echo tx_newspaper_be::renderBackendPageZone($pz, false, true);
		} else {
            tx_newspaper_PageZone::updateDependencyTree($pz);
        }

		die();
	}

    private function processExtraCreate($article_uid, $extra_class, $origin_uid = 0, $pz_uid, $paragraph, $show = 1) {
        $extra = new $extra_class;
        $extra->setAttribute('crdate', time());
        $extra->setAttribute('tstamp', time());
        $extra->setAttribute('cruser_id', tx_newspaper::getBeUserUid());
        $extraUid = $extra->store();
        $extra->setAttribute('paragraph', $paragraph);
        $extra->setAttribute('show_extra', $show);
        $extra->setDefaultValues(); // set default values set in TCA
        $extra->store();

        $article = new tx_newspaper_Article($article_uid);
        $article->addExtra($extra);

        $pz_uid = $article->getPageZoneUid();
        $pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
        $pz->moveExtraAfter($extra, $origin_uid);
        $data = array('extra_uid' => $extraUid, 'content' => tx_newspaper_be::renderBackendPageZone($pz, false, true));
        header('Content-type: application/json');
        echo json_encode($data);
        die();
    }

	/// called via ajax: toggle show checkbox for extra on pagezone
	/** \param $extra_uid uid of extra
	 * \param $show boolean value wheater to show or not this extra
     */
	private function processExtraSetShow($extra_uid, $show) {
		$e = tx_newspaper_Extra_Factory::getInstance()->create(intval($extra_uid));
		$e->setAttribute('show_extra', $show);
		$e->store();
        tx_newspaper_Extra::updateDependencyTree($e);
		die();
	}

	/// called via ajax: create an extra (using a shortcut link in "extra in article"); is used in concrete articles only
	/** \param $article_uid article uid
	 *  \param $extra_class name of extra class (needed if $extra_uid is 0)
	 *  \param $extra_uid if not 0 the uid of the abstract extra to be duplicated
	 *  \param $paragraph paragraph for (non-duplicated) extras
     */
	private function processExtraShortcutCreate($article_uid, $extra_class, $extra_uid, $paragraph, $show = 1) {
//t3lib_div::devlog('processExtraShortcurtCreate()', 'newspaper', 0, array('article_uid' => $article_uid, 'extra class' => $extra_class, 'extra uid' => $extra_uid, 'paragraph' => $paragraph));
		$extra_uid = intval($extra_uid);
		$paragraph = intval($paragraph);
		$article = new tx_newspaper_Article(intval($article_uid));

		if ($extra_uid) {
			// extra uid is set, so duplicate this extra
			$article->addExtra(tx_newspaper_Extra_Factory::getInstance()->create($extra_uid)->duplicate());
		} else {
			// no extra uid set, so create new and empty extra
			if (class_exists($extra_class)) {
				$e = new $extra_class();
				$e->setAttribute('crdate', time());
	 			$e->setAttribute('tstamp', time());
	 			$e->setDefaultValues(); // set default values set in TCA
				// Call store() so that the Extra is persistent, so that setAttribute() can see that
				// attribute 'paragraph' belongs to extra_attributes
				$extra_uid = $e->store();
				$e->setAttribute('paragraph', $paragraph);
                $e->setAttribute('show_extra', $show);
				$extra_uid = $e->store();

				$article->addExtra($e);
			} else {
				throw new tx_newspaper_WrongClassException('Unknown Extra class: ' . $extra_class);
			}
		}

        $htmlContent = tx_newspaper_be::renderBackendPageZone(tx_newspaper_PageZone_Factory::getInstance()->create($article->getAbstractUid()), false, true); // function is called in concrete articles only
        $data = array('extra_uid' => $extra_uid, 'htmlContent' => $htmlContent);
        header('Content-type: application/json');
        echo json_encode($data);
		die();
	}




	private function processExtraSetPassDown($pz_uid, $extra_uid, $pass_down) {
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
		$e = tx_newspaper_Extra_Factory::getInstance()->create(intval($extra_uid));
		$pz->setInherits($e, $pass_down);
//		$e->setAttribute('is_inheritable', $pass_down);
//		$e->store();
        tx_newspaper_Extra::updateDependencyTree($e);
		die();
	}

	private function processSaveExtraField($pz_uid, $extra_uid, $value, $type) {
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
		$e = tx_newspaper_Extra_Factory::getInstance()->create(intval($extra_uid));
		switch(strtolower($type)) {
			case 'para':
				$e->setAttribute('position', 0); // move as first element to new paragraph
				$pz->changeExtraParagraph($e, intval($value)); // change paragraph (and inherit the change); this function stores the extra (so the position change is stored there)
			break;
			case 'notes':
				$e->setAttribute('notes', $value);
				$e->store();
			break;
			default:
				die('Unknown type when saving field: ' + $type);
		}

		// re-read pagezone, changeExtraParagraph() does NOT modify the extra paragraph in the pagezone_article object; see correspoding todo
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));

		if ($pz->isConcreteArticle()) {
			echo tx_newspaper_be::renderBackendPageZone($pz, false);
		}

		die();
	}

	private function processTemplateSetDropdownStore($table, $uid, $value) {
		$uid = intval($uid);

		if (strtolower($table) == 'tx_newspaper_extra') {
            $extra = tx_newspaper_Extra_Factory::getInstance()->create($uid);
            self::setTemplateSet($extra, $value);
            tx_newspaper_Extra::updateDependencyTree($extra);
        } else {
            $obj = new $table($uid);
            self::setTemplateSet($obj, $value);
		}

		die();
	}

    private static function setTemplateSet(tx_newspaper_StoredObject $obj, $value) {
        try {
            $obj->setAttribute('template_set', $value);
            $obj->store();
        } catch (tx_newspaper_Exception $e) {
            die($e->getMessage());
        }
    }


	private function check4Ajax() {
		/// \todo: check permissions
//t3lib_div::devlog('_request mod3 ajax', 'newspaper', 0, array('request' => $_REQUEST, '_server[script_name]' => $_SERVER['SCRIPT_NAME']));

		// delete etxra
		if (t3lib_div::_GP('extra_delete') == 1) {
			$this->processExtraDelete(t3lib_div::_GP('pz_uid'), t3lib_div::_GP('extra_uid'));
		}

		// insert extra
		if (t3lib_div::_GP('extra_insert_after') == 1) {
			$this->processExtraInsertAfter(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('pz_uid'));
		}

		// move etxra
		if (t3lib_div::_GP('extra_move_after') == 1) {
			$this->processExtraMoveAfter(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('pz_uid'), t3lib_div::_GP('extra_uid'));
		}

		// create extra using a shortcut link in extra in article
		if (t3lib_div::_GP('extra_shortcut_create') == 1) {
			$this->processExtraShortcutCreate(t3lib_div::_GP('article_uid'), t3lib_div::_GP('extra_class'), t3lib_div::_GP('extra_uid'), t3lib_div::_GP('paragraph'), t3lib_div::_GP('doShow'));
		}



		// reload list of extras (for concrete article)
		if (t3lib_div::_GP('reload_extra_in_concrete_article') == 1) {
			$this->processReloadExtaInConcreteArticle(t3lib_div::_GP('pz_uid'));
		}




		// store template set
		if (t3lib_div::_GP('templateset_dropdown_store') == 1) {
			die($this->processTemplateSetDropdownStore(t3lib_div::_GP('table'), t3lib_div::_GP('uid'), t3lib_div::_GP('value')));
		}





		if (t3lib_div::_GP('toggle_show_levels_above') == 1) {
			$this->processToggleShowLevelsAbove(t3lib_div::_GP('checked'));
		}

		if (t3lib_div::_GP('toggle_show_visible_only') == 1) {
			$this->processToggleShowVisibleOnly(t3lib_div::_GP('checked'));
		}

		if (t3lib_div::_GP('extra_insert_after_dummy') == 1) {
/// \todo: remove after testing
			$this->processExtraInsertAfterDummy(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('pz_uid'));
		}


//t3lib_div::devlog('params in mod3', 'newspaper', 0, array('input' => $this->input));

		switch($this->input['ajaxController']) {
		// clipboard functions
			case 'cutClipboard':
				tx_newspaper_be::copyExtraToClipboard($this->input, true);
				die();
			break;
			case 'copyClipboard':
				tx_newspaper_be::copyExtraToClipboard($this->input);
				die();
			break;
			case 'clearClipboard':
				tx_newspaper_be::clearClipboard();
				die();
			break;
			case 'pasteClipboard':
				tx_newspaper_be::processPasteFromClipboard($this->input);
				die();
			break;
		}


		if (t3lib_div::_GP('extra_set_show') == 1) {
			$this->processExtraSetShow(t3lib_div::_GP('extra_uid'), t3lib_div::_GP('show'));
		}

		if (t3lib_div::_GP('extra_set_pass_down') == 1) {
			$this->processExtraSetPassDown(t3lib_div::_GP('pz_uid'), t3lib_div::_GP('extra_uid'), t3lib_div::_GP('pass_down'));
		}

		if (t3lib_div::_GP('extra_page_type_change') == 1) {
			$this->processPageTypeChange(t3lib_div::_GP('pt_uid'));
		}

		if (t3lib_div::_GP('extra_pagezone_type_change') == 1) {
			$this->processPageZoneTypeChange(t3lib_div::_GP('pzt_uid'));
		}


		if (t3lib_div::_GP('inheritancesource_change') == 1) {
			$this->processInheritanceSourceChange(t3lib_div::_GP('pz_uid'), t3lib_div::_GP('value'));
		}


		if (t3lib_div::_GP('extra_save_field') == 1) {
			$this->processSaveExtraField(t3lib_div::_GP('pz_uid'), t3lib_div::_GP('extra_uid'), t3lib_div::_GP('value'), t3lib_div::_GP('type'));
		}


		if (t3lib_div::_GP('chose_extra') == 1) {
			die($this->getChoseExtraForm(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('pz_uid'), t3lib_div::_GP('paragraph'), t3lib_div::_GP('new_at_top')));
		}
		if (t3lib_div::_GP('chose_extra_from_pool') == 1) {
			die($this->getChoseExtraFromPoolForm(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('extra'), t3lib_div::_GP('pz_uid'), t3lib_div::_GP('paragraph')));
		}

		if (t3lib_div::_GP('extra_insert_after_from_pool_copy') == 1) {
			die($this->processExtraInsertAfterFromPoolCopy(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('extra_class'), t3lib_div::_GP('pooled_extra_uid'), t3lib_div::_GP('pz_uid'), t3lib_div::_GP('paragraph'), t3lib_div::_GP('path')));
		}
		if (t3lib_div::_GP('extra_insert_after_from_pool_ref') == 1) {
			die($this->processExtraInsertAfterFromPoolReference(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('extra_class'), t3lib_div::_GP('pooled_extra_uid'), t3lib_div::_GP('pz_uid'), t3lib_div::_GP('paragraph'), t3lib_div::_GP('path')));
		}

        if (t3lib_div::_GP('extra_create') == 1) {
            die($this->processExtraCreate(t3lib_div::_GP('article_uid'), t3lib_div::_GP('extra_class'), t3lib_div::_GP('origin_uid'), t3lib_div::_GP('pz_uid'), t3lib_div::_GP('paragraph'), t3lib_div::_GP('doShow')));
        }



//t3lib_div::devlog('_request mod3 ajax - NO ajax found', 'newspaper', 0);
//debug('no ajax');
		return; // no ajax request found
	}


	private function getChoseExtraForm($origin_uid, $pz_uid, $paragraph=false, $new_at_top=0) {
		global $LANG;
//debug(array($origin_uid, $pz_uid, $paragraph));

		// convert params, sent by js, so false is given as string, not a boolean
/// \todo: switch to 0/1 instead of false/true
		if ($new_at_top == 'false') {
			$new_at_top = false;
		} else {
			$new_at_top = true;
		}
		if ($paragraph == 'false') {
			$paragraph = false;
		} else {
			$paragraph = intval($paragraph);
		}


		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];

		$this->content .= $this->doc->startPage('');

		$this->content .= $this->getSubModalIconHeader();

		$this->content .= $this->doc->header($LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:title_new_extra', false));

 	 	$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod3/res/'));

		$label['new_extra_new'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_extra_new', false);
		$label['new_extra_from_pool'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_extra_from_pool', false);
		$message['no_extra_selected'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_no_extra_selected', false);

		/// list of registered and allowed extras
		$extra = tx_newspaper_Extra::getAllowedExtras(tx_newspaper_extra::HIDE_IN_PLACEMENT);
//debug($extra, 'e');

		$smarty->assign('LABEL', $label);
		$smarty->assign('MESSAGE', $message);
		$smarty->assign('EXTRA', $extra); // list of extras
		$smarty->assign('LIST_SIZE', max(2, min(12, sizeof($extra)))); /// size at least 2, otherwise list would be rendered as dropdown
		$smarty->assign('ORIGIN_UID', intval($origin_uid));
		$smarty->assign('PZ_UID', intval($pz_uid));

		if ($paragraph === false) {
			// the param is received as string, not boolean ... sent with js
			$smarty->assign('PARAGRAPH_USED', false);
		} else {
			$smarty->assign('PARAGRAPH_USED', true);
			$smarty->assign('PARAGRAPH', intval($paragraph));
		}

		if ($new_at_top === false) {
			// the param is received as string, not boolean ... sent with js
			$smarty->assign('NEW_AT_TOP', false);
		} else {
			$smarty->assign('NEW_AT_TOP', true);
		}


		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
		$smarty->assign('IS_CONCRETE_ARTICLE', $pz->isConcreteArticle());
		if ($pz->isConcreteArticle()) {
			$smarty->assign('ARTICLE_UID', $pz->getUid()); // add article uid to smarty data (needed for reloading after inserting an extra)
		} else {
			$smarty->assign('ARTICLE_UID', -1);
		}

		$html = $smarty->fetch('mod3_new_extra.tmpl');

		$this->content .= $this->doc->section('', $html, 0, 1);
		$this->content .= $this->doc->endPage();

		return $this->content;
	}


	private function getChoseExtraFromPoolForm($origin_uid, $classname, $pz_uid, $paragraph) {
		global $LANG;

		$origin_uid = intval($origin_uid);
		$pz_uid = intval($pz_uid);
		$paragraph = intval($paragraph);


		$e = new $classname(); // instance of a concrete extra

		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];

		$this->content .= $this->doc->startPage('');

		$this->content .= $this->getSubModalIconHeader();

		$this->content .= $this->doc->header($LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:title_new_extra_from_pool', false) . ': ' . $e->getTitle());

		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod3/res/'));

		$label['extra_copy_from_pool'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_extra_copy_from_pool', false);
		$label['extra_reference_from_pool'] =  $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_extra_reference_from_pool', false);
		$message['pool_is_empty'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_pool_is_empty', false);
		$message['no_extra_selected'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_no_extra_selected', false);


		$pooled = $e->getPooledExtras();

		$smarty->assign('EXTRA_POOLED', $pooled);
		$smarty->assign('LABEL', $label);
		$smarty->assign('MESSAGE', $message);
		$smarty->assign('PARAGRAPH', $paragraph);
		$smarty->assign('EXTRA_CLASS', $classname);
		$smarty->assign('PZ_UID', $pz_uid);
		$smarty->assign('ORIGIN_UID', $origin_uid);
		$smarty->assign('LIST_SIZE', max(2, min(12, sizeof($pooled)))); /// size at least 2, otherwise list would be rendered as dropdown

		$html = $smarty->fetch('mod3_new_extra_from_pool.tmpl');

		$this->content .= $this->doc->section('', $html, 0, 1);
		$this->content .= $this->doc->endPage();

		return $this->content;
	}


	/// Renders icons (currently close icon only) for forms in a submodal box
	function getSubModalIconHeader() {
		$html = '<div id="typo3-docheader-row1"><div class="buttonsleft"><div class="buttongroup">';

		$html .= '<a href="#" onclick="top.hidePopWin(false);">' .
			tx_newspaper_BE::renderIcon('gfx/close.gif', '', $GLOBALS['LANG']->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_close', false)) .
			'</a>';

		$html .= '</div></div></div>';

		return $html;
	}


	/// read section_is, page_id and pagezone_id (if possible)
	/** fills $this->section_id, $this->page_id and $this->pagezone_id
	 * \return void
     */
	function readUidList() {
		global $BE_USER;
		/// \todo: check permissions?

		$this->show_levels_above = $BE_USER->getModuleData('tx_newspaper/mod3/index.php/show_levels_above'); // read from be user
		if ($this->show_levels_above !== true) $this->show_levels_above = false; // make sure it's boolean

		$this->show_visible_only = $BE_USER->getModuleData('tx_newspaper/mod3/index.php/show_visible_only'); // read from be user
		if ($this->show_visible_only !== true) $this->show_visible_only = false; // make sure it's boolean


		// init
		$this->section_id = 0;
		$this->page_type_id = 0;
		$this->pagezone_type_id = 0;

		$this->page_type_id = 0;
		$this->pagezone_type_id = 0;


		/// process section id
		if ($this->id) {
			$this->section_id = $this->id; // clicked in section tree
		} else if ($BE_USER->getModuleData("tx_newspaper/mod3/index.php/section_id")){
			$this->section_id = $BE_USER->getModuleData("tx_newspaper/mod3/index.php/section_id"); // read from be user
		}
		if ($this->section_id) {
			$s = new tx_newspaper_Section(intval($this->section_id));

			try {
				$isValidSection = $s->isValid();
			} catch ( tx_newspaper_EmptyResultException $e) {
				t3lib_div::devlog('readUidList() - invalid section', 'newspaper', 3, array('section uid' => $this->section_id));
				$isValidSection = false; // for some reason the section stored in the be_user isn't valid
			}

			if (!$isValidSection) {
				// no valid section, nothing to show ...
				$this->section_id = 0;
			}
		}
//debug(t3lib_div::view_array($s));


		/// process page type id
		if ($this->section_id) {
			$active_pages = $s->getSubPages(); /// get list of available pages for given section
			if ($BE_USER->getModuleData("tx_newspaper/mod3/index.php/page_type_id")) {
				// check if page with stored page type is available for given section
				$this->page_type_id = $BE_USER->getModuleData("tx_newspaper/mod3/index.php/page_type_id"); // read from be user
				$pt = new tx_newspaper_PageType(intval($this->page_type_id));

				try {
					$isValidPageType = $pt->isValid();
				} catch ( tx_newspaper_EmptyResultException $e) {
					t3lib_div::devlog('readUidList() - invalid pagetype', 'newspaper', 3, array('pagetype uid' => $this->page_type_id));
					$isValidPageType = false; // for some reason the page type stored in the be_user isn't valid
				}


				if ($isValidPageType) {
					/// get page with given page type
					for ($i = 0; $i < sizeof($active_pages); $i++) {
						if ($active_pages[$i]->getPageType()->getUid() == $this->page_type_id) {
							$this->page_id = $active_pages[$i]->getUid();
							$p = $active_pages[$i]; /// save page object, needed for page zone check
							break;
						}
					}
				} else {
					// stored page type isn't valid
					$this->page_type_id = 0;
					$this->page_id = 0;
				}
			}
			if (!$this->page_id) {
				/// no page found so far, try to use first available page (as default value)
				if (sizeof($active_pages) > 0) {
					$this->page_type_id = $active_pages[0]->getPageType()->getUid();
					$this->page_id = $active_pages[0]->getUid();
					$p = $active_pages[0]; /// save page object, needed for page zone check
				}
			}
		}
//debug(t3lib_div::view_array($p));


		/// process pagezone type id
		if ($this->page_id) {
			$active_pagezones = $p->getPageZones(); /// get list of available page zones for given page
//debug(t3lib_div::view_array($active_pagezones[0]));
			if ($BE_USER->getModuleData("tx_newspaper/mod3/index.php/pagezone_type_id")) {
				// check if page zone with stored pagezone type is available for given page
				$this->pagezone_type_id = $BE_USER->getModuleData("tx_newspaper/mod3/index.php/pagezone_type_id"); // read from be user
				$pzt = new tx_newspaper_PageZoneType(intval($this->pagezone_type_id));

				try {
					$isValidPageZoneType = $pzt->isValid();
				} catch ( tx_newspaper_EmptyResultException $e) {
					t3lib_div::devlog('readUidList() - invalid pagezonetype', 'newspaper', 3, array('pagezonetype uid' => $this->pagezone_type_id));
					$isValidPageZoneType = false; // for some reason the pagezonetype stored in the be_user isn't valid
				}

				if (is_array($active_pagezones) && $isValidPageZoneType) { ///  if no active pagezone is available, true is returned!
					/// get pagezone with given pagezone type
					for ($i = 0; $i < sizeof($active_pagezones); $i++) {
						if ($active_pagezones[$i]->getPageZoneType()->getUid() == $this->pagezone_type_id) {
							$this->pagezone_id = $active_pagezones[$i]->getAbstractUid();
							break;
						}
					}
				} else {
					// stored pagezone type isn't valid
					$this->pagezone_type_id = 0;
					$this->pagezone_id = 0;
				}
			}
			if (!$this->pagezone_id) {
				/// no pagezone found so far, try to use first available pagezone (as default value)
				if (is_array($active_pagezones) && sizeof($active_pagezones) > 0) {
					$this->pagezone_type_id = $active_pagezones[0]->getPageZoneType()->getUid();
					$this->pagezone_id = $active_pagezones[0]->getAbstractUid();
				}
			}
		}

		/// store ids for be user for later use
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/section_id", $this->section_id);
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/page_type_id", $this->page_type_id);
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/pagezone_type_id", $this->pagezone_type_id);

	}



				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
				 */
				function main()	{

					$this->input = t3lib_div::GParrayMerged($this->prefixId); // store params

					$this->check4Ajax(); /// if this is an ajax call, the request gets process and execution of this file ends with die()

					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					$access = $GLOBALS['BE_USER']->user['uid']? true : false; // \todo: better check needed

					if ($access)	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('fullWidthDoc_mod3');
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

						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />' . $LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage('');

						// Render content:
						$this->moduleContent();

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
				function moduleContent() {
					global $LANG;

					/// check if at least one section page type and page zone type are available. if not, this module is senseless.
					if (!tx_newspaper::atLeastOneRecord('tx_newspaper_section')) {
						die($LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_section_available', false));
					}
					if (!tx_newspaper::atLeastOneRecord('tx_newspaper_pagetype')) {
						die($LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_pagetype_available', false));
					}
					if (!tx_newspaper::atLeastOneRecord('tx_newspaper_pagezonetype')) {
						die($LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_pagezonetype_available', false));
					}

//global $BE_USER; debug(array('section id' => $BE_USER->getModuleData("tx_newspaper/mod3/index.php/section_id"), 'page type id' => $BE_USER->getModuleData("tx_newspaper/mod3/index.php/page_type_id"), 'pagezone type id' => $BE_USER->getModuleData("tx_newspaper/mod3/index.php/pagezone_type_id")));
					$this->readUidList(); // get ids for section, page and pagezone
//debug(array('section id' => $this->section_id, 'page type id' => $this->page_type_id, 'pagezone type id' => $this->pagezone_type_id, 'page id' => $this->page_id, 'pagezone id' => $this->pagezone_id));
//debug($_REQUEST);

					if (!$this->section_id) {
						if (tx_newspaper::atLeastOneRecord('tx_newspaper_section')) {
							/// check if at least one section exists
							$this->content .= $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_section_available', false);
						} else {
							/// no section id found, just display message to choose a section from the section tree
							$this->content .= $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_section_chosen', false);
						}
					} else if (!$this->page_id) {
						/// no page has been activated for given section
						$this->content .= $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_page_available_for_section', false);
					} else if (!$this->pagezone_id) {
						/// no pagezone has been activated for given page for given section
						$this->content .= $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_pagetype_available_for_page', false);
					} else {
						/// render form for pagezone
						try {
							$content = tx_newspaper_BE::renderBackendPageZone(
								tx_newspaper_PageZone_Factory::getInstance()->create(intval($this->pagezone_id)),
								$this->show_levels_above
							);
						} catch(tx_newspaper_PathNotFoundException $e) {
							// \todo localization
							die('Templates could\'t be found. Is TSConfig newspaper.defaultTemplate set to the correct path?');
						}

					}

					$this->content .= $this->doc->section('', $content, 0, 1);

				}


	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();

	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		parent::menuConfig();
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod3/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod3/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_newspaper_module3');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>