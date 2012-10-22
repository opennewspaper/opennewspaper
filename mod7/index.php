<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Lene Preuss, Oliver Schröder, Samuel Talleux <lene.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
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

$LANG->includeLLFile('EXT:newspaper/mod7/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);

require_once(PATH_typo3conf . 'ext/newspaper/Classes/private/class.tx_newspaper_placementbe.php');

/// Class to generate a BE module with 100% width
class fullWidthDoc_mod7 extends template {
	var $divClass = 'typo3-fullWidthDoc';	///< Sets width to 100%
}



/**
 * Module 'Article placement' for the 'newspaper' extension.
 *
 * @author	Matthias Krappitz <matthias@aemka.de>, Lene Preuss, Oliver Schröder, Samuel Talleux <lene.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 * @package	TYPO3
 * @subpackage	tx_newspaper
 */
class  tx_newspaper_module7 extends t3lib_SCbase {
				var $pageinfo;
				var $prefixId = 'tx_newspaper_mod7';

                /** @var tx_newspaper_BE */
				private $al_be = null; // backend object providing the methods for generating the backend for article list placement

                const DEBUG = false; // @todo User TSConfig

				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					parent::init();
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

// \todo: better check: currently access to all be_users is granted
					if (isset($GLOBALS['BE_USER']->user['uid']) && $GLOBALS['BE_USER']->user['uid']) {
//						ini_set('display_errors',  'on');

						$this->al_be = new tx_newspaper_BE();

						// get "pi"vars
						$input = t3lib_div::GParrayMerged($this->prefixId);
						if (!isset($input['articleid'])) {
							$input['articleid'] = 0; //isset($input['placearticleuid'])? $input['placearticleuid'] : 0; // needed for standalone form (singleplacement)
						}
//t3lib_div::devlog('mod7 main()', 'np', 0, array('input' => $input));
						// handle ajax
						switch ($input['ajaxcontroller']) {
							case 'showplacementandsavesections' :
								$this->saveSectionsForArticle($input);
                                $be = new tx_newspaper_PlacementBE($input);
								die($be->render());
							break;
							case 'updatearticlelist':
								die($this->updateArticlelist($input));
							break;
							case 'checkarticlelistsforupdates' :
								die(json_encode($this->checkArticleListsForUpdates($input)));
							break;
							case 'savearticlelist':
								// can be a section or a non-section article list
								// section al, if element is set to placer_[section uid]
								// non-section al, if element is set to al_[al uid]
								die($this->saveSection($input));
							break;
							case 'placearticle':
								die($this->placeArticle($input));
							break;
							case 'placearticlehide':
								die($this->placeArticle($input, array('hide' => true)));
							break;
							case 'placearticlepublish':
								die($this->placeArticle($input, array('publish' => true)));
							break;
							case 'sendarticletodutyeditor':
								die($this->sendArticleToDutyEditor($input));
							break;
							case 'sendarticletodutyeditorhide':
								die($this->sendArticleToDutyEditor($input, array('hide' => true)));
							break;
							case 'sendarticletodutyeditorpublish':
								die($this->sendArticleToDutyEditor($input, array('publish' => true)));
							break;
							case 'sendarticletoeditor':
								die($this->sendArticleToEditor($input));
							break;
							case 'sendarticletoeditorhide':
								die($this->sendArticleToEditor($input, array('hide' => true)));
							break;
							case 'sendarticletoeditorpublish':
								die($this->sendArticleToEditor($input, array('publish' => true)));
							break;
							case 'putarticleonline':
								die($this->putArticleOnline($input));
							break;
							case 'putarticleoffline':
								die($this->putArticleOffline($input));
							break;
							case 'justsave' :
								die(true);
							break;
                            case 'top':
                            case 'bottom':
                            case 'moveup':
                            case 'movedown':
                                die($this->resortArticlelist($input, $input['ajaxcontroller']));
                            break;
						}

						// draw the header
						$this->doc = t3lib_div::makeInstance('fullWidthDoc_mod7');
						$this->doc->backPath = $BACK_PATH;
						$this->content .= $this->doc->startPage($LANG->getLL('title'));

						$output = '';
						switch ($input['controller']) {
							case 'preview' :
								$output = $this->renderPreview($input['articleid']);
    							break;
							case 'placement' :
                                $be = new tx_newspaper_PlacementBE($input);
								$output = $be->render();
	    						break;
							case 'singleplacement' :
                                $be = new tx_newspaper_PlacementBE($input);
								$output = $be->renderSingle();
		    					break;
							default :
								$output = $this->renderModule($input);
			    				break;
						}
						$output = $this->be_wrapInBaseClass($output);

						$this->content .= $this->doc->section('', $output, 0, 1);
						$this->moduleContent();
						$this->content .= $this->doc->spacer(10);
					} else {
						// If no access or if ID == zero

						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;

						$this->content .= $this->doc->startPage($LANG->getLL('title'));
						$this->content .= $this->doc->header($LANG->getLL('title'));
						$this->content .= $this->doc->spacer(5);
						$this->content .= $this->doc->spacer(10);
					}
				}


				function be_wrapInBaseClass ($content) {
					return '<div class="' . $this->prefixId . '">' . $content . '</div>';
				}


				// render a frontend preview of an article
				function renderPreview ($articleId) {
					$article = new tx_newspaper_Article($articleId);
					return $article->render();
				}


				// render the main / full module
				function renderModule ($input) {
					// check if article placement for article was called
					if (isset($input['articleid']) && $input['articleid']) {
						return $this->renderPlacementModule($input);
					}

					// render article list backend module
					return $this->renderArticlelistList($input);
				}

				/// render list of all (non-section) article lists
				function renderArticlelistList($input) {
					$al = $this->getAllNonSectionArticleLists();
//t3lib_div::devlog('getAllNonSectionArticleLists()', 'newspaper', 0, array('al' => $al));

					// get ll labels
					$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod7/locallang.xml', $GLOBALS['LANG']->lang);
					$localLang = $localLang[$GLOBALS['LANG']->lang];

					// prepare: icons ...
					$al_be = new tx_newspaper_BE();

					// instanciate smarty
					$smarty = new tx_newspaper_Smarty();
					$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod7/res/'));
					$smarty->assign('input', $input);
					$smarty->assign('DEBUG', self::DEBUG);
					$smarty->assign('lang', $localLang);
					$smarty->assign('AL', $al);
					$smarty->assign('ICON', $al_be->getArticleListIcons());
					$smarty->assign('T3PATH', tx_newspaper::getAbsolutePath(true));
					return $smarty->fetch('mod7_articlelist_list.tmpl');
				}

				/// \return array with concrete article lists
				// \todo: move to articlelist class?
				private function getAllNonSectionArticleLists() {
					$row = tx_newspaper::selectRows(
						'uid',
						'tx_newspaper_articlelist',
						'(section_id IS NULL OR section_id="")', // non-section articlelists only
						'',
						'sorting, uid'
					);
					$articlelist = array();
					foreach($row as $current_al) {
						$articlelist[] = tx_newspaper_ArticleList_Factory::getInstance()->create(intval($current_al['uid']));
					}
					return $articlelist;
				}



				/// render backend for placing an article into all article lists (depending on the chosen setions)
				function renderPlacementModule($input) {
					// get data
					$article = new tx_newspaper_Article($input['articleid']);
					$sections = $this->renderAllAvailableSections();
					$sections_active = $this->renderSectionsForArticle($article);
					$backendUser = $this->getBackendUserById($article->getAttribute('modification_user'));
					// get ll labels
					$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod7/locallang.xml', $GLOBALS['LANG']->lang);
					$localLang = $localLang[$GLOBALS['LANG']->lang];

					// instanciate smarty
					$smarty = new tx_newspaper_Smarty();
					$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod7/res/'));
					$smarty->assign('input', $input);
					$smarty->assign('article_workflow_status_title', tx_newspaper_workflow::getRoleTitle($article->getAttribute('workflow_status')));
//t3lib_div::devlog('mod7', 'newspaper', 0, array('article' => $article));
					$smarty->assign('article', $article);
					$smarty->assign('sections', $sections);
					$smarty->assign('sections_active', $sections_active);
					$smarty->assign('backenduser', $backendUser);
					$smarty->assign('lang', $localLang);
					$smarty->assign('ICON', $this->al_be->getArticlelistIcons());
					$smarty->assign('SPINNER', tx_newspaper::getAbsolutePath() . 'typo3conf/ext/newspaper/mod7/res/move-spinner.gif');
					$smarty->assign('workflow_permissions', array(
						'hide' => ($article->getAttribute('hidden'))? false : tx_newspaper_workflow::isFunctionalityAvailable('hide'),
						'publish' => (!$article->getAttribute('hidden'))? false : tx_newspaper_workflow::canPublishArticles(),
						'check' => ($article->getAttribute('workflow_status') == 1)? false : tx_newspaper_workflow::isFunctionalityAvailable('check'),
						'revise' => ($article->getAttribute('workflow_status') == 0)? false : tx_newspaper_workflow::isFunctionalityAvailable('revise'),
						'place' => tx_newspaper_workflow::canPlaceArticles() || tx_newspaper_Workflow::mayPlaceAsEditor(),
					));
					$smarty->assign('workflowlog',
						tx_newspaper_workflow::getJavascript() .
						tx_newspaper_workflow::renderBackend('tx_newspaper_article', $input['articleid'], false, true)
					);
					return $smarty->fetch('mod7_module.tpl');
				}



				// save all the selected sections for an article
				function saveSectionsForArticle($input) {
					$sectionIds = array();
					// we take all the sections out of the strings like 10|11|12, 10|14|17, ...
					if (is_array($input['sections_selected'])) {
						foreach ($input['sections_selected'] as $sectionCombination) {
							$sectionCombination = explode('|', $sectionCombination);
							$sectionUid = $sectionCombination[sizeof($sectionCombination)-1];
							if (!in_array($sectionUid, $sectionIds)) {
								$sectionIds[] = $sectionUid; // append last section uid, ignore the section tree above
							}
						}
						$article = new tx_newspaper_article($input['placearticleuid']);
// \todo: why is setSection() called when the form is built (setSection deleted and re-stored section ...)
						return $article->setSections($sectionIds);
					}
				}




/// modifying the workflow status of an article

				/// place an article
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function placeArticle($input, array $statusHidePublish=array()) {

                    $timer = tx_newspaper_ExecutionTimer::create();

                    tx_newspaper_ExecutionTimer::start();

					$article = new tx_newspaper_Article($input['placearticleuid']);
					$article->setAttribute('workflow_status', NP_ACTIVE_ROLE_NONE);

                    tx_newspaper_ExecutionTimer::logExecutionTime('placeArticle(): first block');

                    tx_newspaper_ExecutionTimer::start();

					$_REQUEST['workflow_status'] = NP_ACTIVE_ROLE_NONE; // \todo: well, this is a hack (simulating a submit)
					$log['place'] = true;
                    $this->updatePublishingStatus($statusHidePublish, $article, $log);
					$this->writeLog($input, $log);

                    tx_newspaper_ExecutionTimer::logExecutionTime('placeArticle(): second block');

					return true;
				}

    private function updatePublishingStatus(array $statusHidePublish, tx_newspaper_Article $article, array &$log) {

        $timer = tx_newspaper_ExecutionTimer::create();

        if (isset($statusHidePublish['hide'])) {
            $log['hidden'] = true;
            $this->hideArticle($article);
        } elseif (isset($statusHidePublish['publish'])) {
            $log['hidden'] = false;
            $this->publishArticle($article);
        }

        $article->store();
    }

    /// send article further to duty editor
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function sendArticleToDutyEditor($input, array $statusHidePublish=array()) {
					$article = new tx_newspaper_Article($input['placearticleuid']);
					$article->setAttribute('workflow_status', NP_ACTIVE_ROLE_DUTY_EDITOR);
					$_REQUEST['workflow_status'] = NP_ACTIVE_ROLE_DUTY_EDITOR; // \todo: well, this is a hack ...
					$log = array(
						'workflow_status' => NP_ACTIVE_ROLE_DUTY_EDITOR,
						'workflow_status_ORG' => $input['workflow_status_ORG']
					);
					if (isset($statusHidePublish['hide'])) {
						$log['hidden'] = true;
						$this->hideArticle($article);
					} elseif (isset($statusHidePublish['publish'])) {
						$log['hidden'] = false;
						$this->publishArticle($article);
					}
					$article->store();
					$this->writeLog($input, $log);
					return true;
				}

				/// send article back to editor
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function sendArticleToEditor($input, array $statusHidePublish=array()) {
					$article = new tx_newspaper_Article($input['placearticleuid']);
					$article->setAttribute('workflow_status', NP_ACTIVE_ROLE_EDITORIAL_STAFF);
					$_REQUEST['workflow_status'] = NP_ACTIVE_ROLE_EDITORIAL_STAFF; // \todo: well, this is a hack ...
					$log = array(
						'workflow_status' => NP_ACTIVE_ROLE_EDITORIAL_STAFF,
						'workflow_status_ORG' => $input['workflow_status_ORG']
					);
					if (isset($statusHidePublish['hide'])) {
						$log['hidden'] = true;
						$this->hideArticle($article);
					} elseif (isset($statusHidePublish['publish'])) {
						$log['hidden'] = false;
						$this->publishArticle($article);
					}
					$article->store();
					$this->writeLog($input, $log);
					return true;
				}

				/// set article status to online
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function putArticleOnline($input) {
					$article = new tx_newspaper_Article($input['placearticleuid']);
					$log['hidden'] = false;
					$this->publishArticle($article);
					$this->writeLog($input, $log);
					return true;
				}

				/// set article status to offline
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function putArticleOffline($input) {
					$article = new tx_newspaper_Article($input['placearticleuid']);
					$log['hidden'] = true;
					$this->hideArticle($article);
					$this->writeLog($input, $log);
					return true;
				}


				private function hideArticle(tx_newspaper_article $article) {
                    $timer = tx_newspaper_ExecutionTimer::create();
					$article->storeHiddenStatusWithHooks(true);
				}

				private function publishArticle(tx_newspaper_article $article) {
                    $timer = tx_newspaper_ExecutionTimer::create();
					$article->storeHiddenStatusWithHooks(false); // this makes sure the publish_date is set correctly (if needed)
				}



				/// write workflow log entry for manual comment (if a manual comment was entered)
				/**
				 *  \param $input data submitted in form
				 *  \return true if a workflow comment was written, else false
				 */
				private function writeLog(array $input, array $type) {
//t3lib_div::devlog('writeLog()','newspaper', 0, array('input' => $input, 'type' => $type));
					if (!isset($input['placearticleuid']) || !intval($input['placearticleuid'])) {
						return false; // no article uid, no log entry ...
					}

					// create a "fake" fieldArray
					$fieldArray = array();
					if (isset($type['hidden'])) {
						$fieldArray['hidden'] = $type['hidden'];
					}
					if (isset($type['workflow_status'])) {
						$fieldArray['workflow_status'] = $type['workflow_status'];
					} elseif (isset($type['place']) && $type['place'] == 1) {
						$fieldArray['workflow_status'] = NP_ACTIVE_ROLE_NONE;
					}
					if (isset($type['workflow_status_ORG'])) {
						$fieldArray['workflow_status_ORG'] = $type['workflow_status_ORG'];
					}
					if (isset($input['workflow_comment'])) {
						$fieldArray['workflow_comment'] = $input['workflow_comment'];
						$_REQUEST['workflow_comment'] = $input['workflow_comment']; // \todo: remove hack, so tx_newspaper_workflow::processAndLogWorkflow() can work
					}
//t3lib_div::devlog('writeLog()','newspaper', 0, array('fake fieldArray' => $fieldArray));
					tx_newspaper_workflow::processAndLogWorkflow('update', 'tx_newspaper_article', intval($input['placearticleuid']), $fieldArray);
					return true;
				}



				/// save all the articles of a single section
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function saveSection($input) {

                    tx_newspaper_ExecutionTimer::logMessage("saveSection: ArticleIDs " . $input['articleids']);
                    $timer = tx_newspaper_ExecutionTimer::create();

                    $articleIds = $offsets = array();
                    self::extractIDsAndOffsets($input, $articleIds, $offsets);

					if (substr($input['element'], 0, 7) == 'placer_') {
                        $this->handleSection($input, $articleIds, $offsets);
					} elseif (substr($input['element'], 0, 3) == 'al_') {
                        $this->handleAL($input, $articleIds, $offsets);
					} else {
                        t3lib_div::devlog('saveSection() - unknown type [element]', 'newspaper', 3, array('input' => $input));
                        return false;
                    }
                    return true;

				}

    /// split offsets from article ids
    private static function extractIDsAndOffsets($input, &$articleIds, &$offsets) {
        $articleIds = $input['articleids'] ? explode('|', $input['articleids']) : array();
        $offsets = array();

        if (isset($articleIds[0]) && strstr($articleIds[0], '_')) {
            for ($i = 0; $i < count($articleIds); ++$i) {
                $articleId = explode('_', $articleIds[$i]);
                $offsets[$i] = $articleId[0];
                $articleIds[$i] = $articleId[1];
            }
        }
    }

    private function handleSection($input, $articleIds, $offsets) {
        // section article list
        $sectionId = $this->al_be->extractElementId($input['element']);
        $section = new tx_newspaper_section ($sectionId);

        self::saveArticleList($section->getArticleList(), $articleIds, $offsets);
    }

    private function handleAL($input, $articleIds, $offsets) {
        // non-section-article list
        $al_uid = intval($this->al_be->extractElementId($input['element']));

        $al = tx_newspaper_ArticleList_Factory::getInstance()->create($al_uid);

        self::saveArticleList($al, $articleIds, $offsets);
    }

    /// save differently depending on list type
    private static function saveArticleList(tx_newspaper_ArticleList $al, $articleIds, $offsets) {

        $timer = tx_newspaper_ExecutionTimer::create();

        switch ($al->getTable()) {
            case 'tx_newspaper_articlelist_manual' :
                $al->assembleFromUIDs($articleIds);
                break;
            case 'tx_newspaper_articlelist_semiautomatic' :
                $articleIdsAndOffsets = array();
                for ($i = 0; $i < count($articleIds); ++$i) {
                    $articleIdsAndOffsets[] = array(
                        $articleIds[$i],
                        (isset($offsets[$i])) ? $offsets[$i] : '0'
                    );
                }
                $al->assembleFromUIDs($articleIdsAndOffsets);
                break;
            default:
                t3lib_div::devlog('Unknown article list type', 'newspaper', 3, $al->getTable());
        }
    }


    /// check several article lists if they have been modified in database
				/** in comparison the the displayed ones in the form
				 *  \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return ?
				 */
				function checkArticleListsForUpdates ($input) {
					$input['sections'] = explode('|', $input['sections']);
					$articleLists = array();
					$result = array();

					// get data for all sections
					foreach ($input['sections'] as $section) {
						$articleLists[$section] = $this->al_be->getArticleListBySectionId($section, $input['placearticleuid']);
					}

					// explode selected values ourselves as we must not use pivars any more
					$selectValues = array();
					$input['sectionvalues'] = explode('/', $input['sectionvalues']);
					for ($i = 0; $i < count($input['sectionvalues']) ; ++$i) {
						$input['sectionvalues'][$i] = explode(':', $input['sectionvalues'][$i]);
						$selectValues[$input['sectionvalues'][$i][0]] = explode('|', $input['sectionvalues'][$i][1]);
					}

					// do a comparison (length and order) between form input and database
					$i = 0;
					foreach ($articleLists as $articleListKey => $articleList) {
						$renderedUids = array();
						foreach ($selectValues[$articleListKey] as $value) {
							if ($value != '') {
								$renderedUids[] = $value;
							}
						}
						$currentUids = array_keys($articleList);
						$result[$articleListKey] = true;
						if (count($renderedUids) != count($currentUids)) {
							$result[$articleListKey] = false;
						}
						if ($result[$articleListKey]) {
							for ($i = 0; $i < count($renderedUids); ++$i) {
								if ($renderedUids[$i] != $currentUids[$i]) {
									$result[$articleListKey] = false;
									break;
								}
							}
						}
						++$i;
					}

					return $result;
				}




				/// get all available sections
				/** Get all sections an article can be assigned to
				 *  \return \code array (
				 * 		"root_section_uid_1|...|current_section_uid_1" => "Root Section 1 > ... > Parent Section 1 > Current Section 1",
				 * 		...,
				 * 		"root_section_uid_N|...|current_section_uid_N" => "Root Section N > ... > Current Section N"
				 *  )
				 */
				function renderAllAvailableSections() {
					return $this->prepareSectionOptions(tx_newspaper_section::getAllSectionsWithRestrictions());
				}

				/// get all sections assigned to given article
				/** Get a list of all sections the article is assigned to
				 * \param $article object containing an article
				 *  \return \code array (
				 * 		"root_section_uid_1|...|current_section_uid_1" => "Root Section 1 > ... > Parent Section 1 > Current Section 1",
				 * 		...,
				 * 		"root_section_uid_N|...|current_section_uid_N" => "Root Section N > ... > Current Section N"
				 *  )
				 */
				function renderSectionsForArticle(tx_newspaper_Article $article) {
				 	return $this->prepareSectionOptions($article->getSections());
				}

				/// render html code to be added to a select box
				/** Render HTML code to be added to the select boxed in the Smarty template
				 * \param $sections array containg section objects
				 * \return \code array (
				 * 		"root_section_uid_1|...|current_section_uid_1" => "Root Section 1 > ... > Parent Section 1 > Current Section 1",
				 * 		...,
				 * 		"root_section_uid_N|...|current_section_uid_N" => "Root Section N > ... > Current Section N"
				 *  )
				 */
				private function prepareSectionOptions(array $sections) {
					$result = array();
					foreach ($sections as $section) {
						$sectionPathes =  $section->getSectionPath();
						$uids = array();
						$titles = array();
						foreach ($sectionPathes as $sectionPath) {
							$uids[] = $sectionPath->getAttribute('uid');
							$titles[] = $sectionPath->getAttribute('section_name');
						}
						$uids = implode('|', array_reverse($uids));
						$titles = implode(' > ', array_reverse($titles));
						$result[$uids] = $titles;
					}
					return $result;
				 }



				function getBackendUserById ($userId) {
					// intentionally done without enableFields check
					$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						'*',
						'be_users',
						'uid = ' . $userId);
					if (count($result) == 1) {
						return $result[0];
					}
					return array();
				}


				function updateArticlelist($input) {
					if (substr($input['element'], 0, 7) == 'placer_') {
						// section article list
						$input['section'] = $input['element']; // section al type
						return json_encode($this->al_be->getArticleListBySectionId($input['section'], $input['placearticleuid']));
					} elseif (substr($input['element'], 0, 3) == 'al_') {
						// non-section article list
						$input['al'] = $input['element']; // non-section al type
						return json_encode($this->al_be->getArticleListByArticlelistId($input['al'], $input['placearticleuid']));
					}
				}

                function resortArticleList($input, $action) {

                    $timer = tx_newspaper_ExecutionTimer::create();

                    $sectionId = $this->al_be->extractElementId($input['element']);
                    $section = new tx_newspaper_section ($sectionId);
                    $articleList = $section->getArticleList();

                    if($articleList instanceof tx_newspaper_ArticleList_Semiautomatic) {

                        $articleList->useOptimizedGetArticles(true);

                        $selectedArticleId = array_pop(explode('_', $input['sel_article_id']));
                        $oldOrder = array();
                        $articleIds = explode('|', $input['articleids']);
                        foreach($articleIds as $articleAndOffset) {
                            //string is offset_articleId, but array should be articleId offset
                            $tmp = explode('_', $articleAndOffset);
                            $oldOrder[] = array($tmp[1], $tmp[0]);
                        }

                        //map action to operation
                        if($action == 'moveup') {
                                $action = 1;
                        } else if($action == 'movedown') {
                            $action = -1;
                        } else if($action == 'top') {
                            $action = tx_newspaper_Articlelist_Operation::TOP_STRING;
                        } else if($action == 'bottom') {
                            $action = tx_newspaper_Articlelist_Operation::BOTTOM_STRING;
                        }

//        t3lib_div::devlog('mod7 resort', 'np', 0, array('selectedArticleId' => $selectedArticleId, 'old order' => $oldOrder));

                        $alOperation = new tx_newspaper_Articlelist_Operation($selectedArticleId, $action);
                        $reorderList = $articleList->resort($oldOrder, $alOperation);

//        t3lib_div::devlog('mod7 resort', 'np', 0, array('selectedArticleId' => $selectedArticleId, 'new order' => $reorderList));

                        //order must be [0] = offset, [1] = articleId
                        for($i = 0; $i < sizeof($reorderList) ; $i++) {
                            $reorderList[$i] = array($reorderList[$i][1],$reorderList[$i][0]);
                        }

                    }
                    return json_encode($reorderList);
                }


				/**
				 * Prints out the module HTML
				 *
				 * @return	void
				 */
				function printContent()	{
					$this->content .= $this->doc->endPage();
					echo $this->content;
				}


				/**
				 * Generates the module content
				 *
				 * @return	void
				 */
				function moduleContent()	{
					switch((string)$this->MOD_SETTINGS['function'])	{
						case 1:
							$content = '';
							$this->content .= $this->doc->section('', $content, 0, 1);
						break;
					}
				}
			}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod7/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod7/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_newspaper_module7');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>