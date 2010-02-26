<?php 
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Helge Preuss, Oliver Schr��der, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
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


/// Class to generate a BE module with 100% width
class fullWidthDoc extends template {
	var $divClass = 'typo3-fullWidthDoc';	///< Sets width to 100%
}



/**
 * Module 'Article placement' for the 'newspaper' extension.
 *
 * @author	Matthias Krappitz <matthias@aemka.de>, Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 * @package	TYPO3
 * @subpackage	tx_newspaper
 */
class  tx_newspaper_module7 extends t3lib_SCbase {
				var $pageinfo;
				var $prefixId = 'tx_newspaper_mod7';

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
						
						// get "pi"vars
						$input = t3lib_div::GParrayMerged($this->prefixId);
//t3lib_div::devlog('mod7 main()', 'np', 0, array('input' => $input));
						// handle ajax
						switch ($input['ajaxcontroller']) {
							case 'showplacementandsavesections' :
								$this->saveSectionsForArticle($input);
								die($this->renderPlacement($input, false));
							break;
							case 'updatearticlelist' :
								die(json_encode($this->getArticleListBySectionId($input['section'], $input['placearticleuid'])));
							break;
							case 'checkarticlelistsforupdates' :
								die(json_encode($this->checkArticleListsForUpdates($input)));
							break;
							case 'savesection' :
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
						}
						// draw the header
						$this->doc = t3lib_div::makeInstance('fullWidthDoc');
						$this->doc->backPath = $BACK_PATH;
						$this->content .= $this->doc->startPage($LANG->getLL('title'));
						
						$output = '';
						switch ($input['controller']) {
							case 'preview' :
								$output = $this->renderPreview($input['articleid']);
							break;
							case 'placement' :
								$output = $this->renderPlacement($input, false);
							break;
							case 'singleplacement' :
								$output = $this->renderSinglePlacement($input['articleid'], $input['sectionid']);
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

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->spacer(10);
					}
				}
				
				
				function be_wrapInBaseClass ($content) {
					return '<div class="' . $this->prefixId . '">' . $content . '</div>';
				}


				// render a frontend preview of an article
				function renderPreview ($articleId) {
					$article = new tx_newspaper_article($articleId);
					// @todo: does not work:
					return $article->render();
				}
				
				
				// render the main / full module
				function renderModule ($input) {
					
					if (!isset($input['articleid']) || !$input['articleid']) {
						die('<div style="margin-left:10px;"><br /><strong>Newspaper message:</strong><br />The article placement module cannot be called directly.</div>');
					}
					
					// get data
					$article = $this->getArticleByArticleId($input['articleid']);
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
					$smarty->assign('ICON', $this->getIcons());
					$smarty->assign('workflow_permissions', array(
						'hide' => ($article->getAttribute('hidden'))? false : tx_newspaper_workflow::isFunctionalityAvailable('hide'),
						'publish' => (!$article->getAttribute('hidden'))? false : tx_newspaper_workflow::isFunctionalityAvailable('publish'),
						'check' => ($article->getAttribute('workflow_status') == 1)? false : tx_newspaper_workflow::isFunctionalityAvailable('check'),
						'revise' => ($article->getAttribute('workflow_status') == 0)? false : tx_newspaper_workflow::isFunctionalityAvailable('revise'),
						'place' => tx_newspaper_workflow::isFunctionalityAvailable('place'),
					));
					$smarty->assign('workflowlog', 
						tx_newspaper_workflow::getJavascript() .
						tx_newspaper_workflow::renderBackend('tx_newspaper_article', $input['articleid'])
					);
					return $smarty->fetch('mod7_module.tpl');
				}
				
				private function getIcons() {
					global $LANG;
					$icon = array(
						'group_totop' => tx_newspaper_BE::renderIcon('gfx/group_totop.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_group_totop', false, 14, 14)),
						'up' => tx_newspaper_BE::renderIcon('gfx/up.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_up', false, 14, 14)),
						'down' => tx_newspaper_BE::renderIcon('gfx/down.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_down', false, 14, 14)),
						'group_tobottom' => tx_newspaper_BE::renderIcon('gfx/group_tobottom.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_group_tobottom', false, 14, 14)),
						'group_clear' => tx_newspaper_BE::renderIcon('gfx/group_clear.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_group_clear', false, 14, 14)),
						'button_left' => tx_newspaper_BE::renderIcon('gfx/button_left.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_button_left', false, 14, 14)),
						'button_right' => tx_newspaper_BE::renderIcon('gfx/button_right.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_button_right', false, 14, 14)),
						'preview' => tx_newspaper_BE::renderIcon('gfx/zoom.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.preview_article', false)),
					);
					return $icon;
				}
				
				
				// save all the selected sections for an article
				function saveSectionsForArticle ($input) {
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
						return $article->setSections($sectionIds);
					}
				}
				
				


/// modifying the workflow status of an article

				/// place an article
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function placeArticle($input, array $statusHidePublish=array()) {
					$article = $this->getArticleByArticleId ($input['placearticleuid']);
					$article->setAttribute('workflow_status', NP_ACTIVE_ROLE_NONE);
					
					$_REQUEST['workflow_status'] = NP_ACTIVE_ROLE_NONE; // \todo: well, this is a hack (simulating a submit)
					$log['place'] = true;
					if (isset($statusHidePublish['hide'])) {
						$log['hidden'] = true;
						$article->setAttribute('hidden', true);
					} elseif (isset($statusHidePublish['publish'])) {
						$log['hidden'] = false;
						$article->setAttribute('hidden', false);
						$article->setPublishDateIfNeeded(); // make sure the publish_date is set correctly
					}
					$article->store();
					$this->writeLog($input, $log);
					return true;
				}
				
				/// send article further to duty editor
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function sendArticleToDutyEditor($input, array $statusHidePublish=array()) {
					$article = $this->getArticleByArticleId ($input['placearticleuid']);
					$article->setAttribute('workflow_status', NP_ACTIVE_ROLE_DUTY_EDITOR);
					$_REQUEST['workflow_status'] = NP_ACTIVE_ROLE_DUTY_EDITOR; // \todo: well, this is a hack ...
					$log = array(
						'workflow_status' => NP_ACTIVE_ROLE_DUTY_EDITOR,
						'workflow_status_ORG' => $input['workflow_status_ORG'] 
					);
					if (isset($statusHidePublish['hide'])) {
						$log['hidden'] = true;
						$article->setAttribute('hidden', true);
					} elseif (isset($statusHidePublish['publish'])) {
						$log['hidden'] = false;
						$article->setAttribute('hidden', false);
						$article->setPublishDateIfNeeded(); // make sure the publish_date is set correctly
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
					$article = $this->getArticleByArticleId ($input['placearticleuid']);
					$article->setAttribute('workflow_status', NP_ACTIVE_ROLE_EDITORIAL_STAFF);
					$_REQUEST['workflow_status'] = NP_ACTIVE_ROLE_EDITORIAL_STAFF; // \todo: well, this is a hack ...
					$log = array(
						'workflow_status' => NP_ACTIVE_ROLE_EDITORIAL_STAFF,
						'workflow_status_ORG' => $input['workflow_status_ORG']
					);
					if (isset($statusHidePublish['hide'])) {
						$log['hidden'] = true;
						$article->setAttribute('hidden', true);
					} elseif (isset($statusHidePublish['publish'])) {
						$log['hidden'] = false;
						$article->setAttribute('hidden', false);
						$article->setPublishDateIfNeeded(); // make sure the publish_date is set correctly
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
					$article = $this->getArticleByArticleId ($input['placearticleuid']);
					$article->setAttribute('hidden', 0);
					$article->setPublishDateIfNeeded(); // make sure the publish_date is set correctly
					$article->store();
					$this->writeLog($input, array(
						'hidden' => false
					));
					return true;
				}
				
				/// set article status to offline
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function putArticleOffline($input) {
					$article = $this->getArticleByArticleId ($input['placearticleuid']);
					$article->setAttribute('hidden', 1);
					$article->store();
					$this->writeLog($input, array(
						'hidden' => true
					));
					return true;	
				}
				
				/// write workflow log entry for manual comment (if a manuel comment was entered)
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
						$_REQUEST['workflow_comment'] = $input['workflow_comment']; // \todo: hack, logWorkflow can work
					}
//t3lib_div::devlog('writeLog()','newspaper', 0, array('fake fieldArray' => $fieldArray));					
					tx_newspaper_workflow::logWorkflow('update', 'tx_newspaper_article', intval($input['placearticleuid']), $fieldArray);
					return true;
				}




				
				/// save all the articles of a single section
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function saveSection ($input) {
					$articleIds = explode('|', $input['articleids']);
					$offsets = array();
					
					//split offsets from article ids
					if (isset($articleIds[0]) && strstr($articleIds[0], '_')) {
						for ($i = 0; $i < count($articleIds); ++$i) {
							$articleId = explode('_', $articleIds[$i]);
							$offsets[$i] = $articleId[0];
							$articleIds[$i] = $articleId[1];
						}
					}
					
					$result = false;
					$sectionId = $this->extractSectionId($input['section']);
					$section = new tx_newspaper_section ($sectionId);
					$sectionType = get_class($section->getArticleList());
					
					// save differently depending on list type
					switch ($sectionType) {
						case 'tx_newspaper_ArticleList_Manual' :
							$result = $section->getArticleList()->assembleFromUIDs($articleIds);
						break;
						case 'tx_newspaper_ArticleList_Semiautomatic' :
							$articleIdsAndOffsets = array ();
							for ($i = 0; $i < count($articleIds); ++$i) {
								$articleIdsAndOffsets[] = array(
									$articleIds[$i], 
									(isset($offsets[$i])) ? $offsets[$i] : '0'
								);
							}
							$result = $section->getArticleList()->assembleFromUIDs($articleIdsAndOffsets);
						break;
					}

					return true;
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
						$articleLists[$section] = $this->getArticleListBySectionId($section, $input['placearticleuid']);
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
				
				
				/// render the placement editors according to sections selected for article
				/** in comparison the the displayed ones in the form
				 *  \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return ?
				 */
				function renderPlacement ($input, $singleMode = false) {
					$selection = $input['sections_selected'];
					
					// calculate which / how many placers to show
					$tree = $this->calculatePlacementTreeFromSelection($selection);

					// grab the data for all the placers we need to display
					$tree = $this->fillPlacementWithData($tree, $input['placearticleuid']);
//t3lib_div::devlog('mod7', 'newspaper', 0, array('tree' => $tree));
					// grab the article
					$article = $this->getArticleByArticleId($input['placearticleuid']);
					
					// get locallang labels 
					$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod7/locallang.xml', $GLOBALS['LANG']->lang);
					$localLang = $localLang[$GLOBALS['LANG']->lang];	
									
					// render
					$smarty = new tx_newspaper_Smarty();
					$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod7/res/'));					
					$smarty->assign('tree', $tree);
					$smarty->assign('article', $article);
					$smarty->assign('singlemode', $singleMode);
					$smarty->assign('lang', $localLang);
					$smarty->assign('isde', tx_newspaper_workflow::isDutyEditor());
					$smarty->assign('ICON', $this->getIcons());
					return $smarty->fetch('mod7_placement.tpl');
				}
				
				
				function renderSinglePlacement ($articleId, $sectionId) {
					$input = array(
						'sections_selected' => array($sectionId), 
						'placearticleuid' => $articleId
					);
					
					return $this->renderPlacement($input ,true);
				}
				
				
				/// get article and offset lists for a set of sections
				/**
				 * 
				 */
				function fillPlacementWithData ($tree, $articleId) {
					for ($i = 0; $i < count($tree); ++$i) {
						for ($j = 0; $j < count($tree[$i]); ++$j) {
							for ($k = 0; $k < count($tree[$i][$j]); ++$k) {
								// get data (for title display) for each section
								$tree[$i][$j][$k]['section'] = new tx_newspaper_section($tree[$i][$j][$k]['uid']);
								// add article list and list type for last element only to tree structure
								if (($k+1) == count($tree[$i][$j])) {
									$tree[$i][$j][$k]['listtype'] = get_class($tree[$i][$j][$k]['section']->getArticleList());
									$tree[$i][$j][$k]['articlelist'] = $this->getArticleListBySectionId ($tree[$i][$j][$k]['uid'], $articleId);
									$tree[$i][$j][$k]['article_placed_already'] = array_key_exists($articleId, $tree[$i][$j][$k]['articlelist']); // flag to indicated if the article to be placed has already been placed in current article list
								}
							}
						}
					}
					return $tree;
				}
				
				
				/// extract just the article-uids from an article list
				/**
				 * 
				 */
				function getArticleIdsFromArticleList ($articleList) {
					// collect all article uids
					$articleUids = array();
					foreach ($articleList as $article) {
						$articleUids[] = $article->getAttribute('uid');
					}
					return $articleUids;
				}
				
				
				/// calculate a "minimal" (tree-)list of sections
				/**
				 * 
				 */
				function calculatePlacementTreeFromSelection ($selection) {
					$result = array();
					
					//@todo: re-arrange sorting here to achieve different positioning in frontend					
					for ($i = 0; $i < count($selection); ++$i) {
						$selection[$i] = explode('|', $selection[$i]);
						$ressort = array();
						for ($j = 0; $j < count($selection[$i]); ++$j) {
							$ressort[]['uid'] = $selection[$i][$j];
							if(!isset($result[$j]) || !in_array($ressort, $result[$j])) {
								$result[$j][] = $ressort;
							}
						}
					}

					return $result;
				}
				
				
				/// get a list of articles by a section id
				/**
				 * 
				 */
				function getArticleListBySectionId ($sectionId, $articleId = false) {
					
					$result = array();
					$sectionId = $this->extractSectionId($sectionId);
					$section = new tx_newspaper_section($sectionId);
					$listType = get_class($section->getArticleList());
					$articleList = $section->getArticleList()->getArticles(9999);
					
					// get offsets
					if ($listType == 'tx_newspaper_ArticleList_Semiautomatic') {
						$articleUids = $this->getArticleIdsFromArticleList($articleList);
						$offsetList = $section->getArticleList()->getOffsets($articleUids);	
					}
					
					// prepend the article we are working on to list for semiautomatic lists
					if ($listType == 'tx_newspaper_ArticleList_Semiautomatic' && $articleId) {
						$article = $this->getArticleByArticleId($articleId);
						$result['0_' . $article->getAttribute('uid')] = $article->getAttribute('kicker') . ': ' . $article->getAttribute('title');
					}
					
					// fill the section placers from their articlelists
					foreach ($articleList as $article) {
						if ($listType == 'tx_newspaper_ArticleList_Manual') {
							$result[$article->getAttribute('uid')] = $article->getAttribute('kicker') . ': ' . $article->getAttribute('title');
						}
						if ($listType == 'tx_newspaper_ArticleList_Semiautomatic') {
							$offset = $offsetList[$article->getAttribute('uid')];
							if ($offset > 0) {
								$offset = '+' . $offset;
							}
							$result[$offsetList[$article->getAttribute('uid')] . '_' . $article->getAttribute('uid')] = $article->getAttribute('kicker') . ': ' . $article->getAttribute('title') . ' (' . $offset . ')';
						}
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
					return $this->prepareSectionOptions(tx_newspaper_section::getAllSections());
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


				
				/// extract the section uid out of the select elements mames that are
				/** like "placer_10_11_12" where we need the "12" out of it
				 * 
				 */
				function extractSectionId ($sectionId) {
					if (strstr($sectionId, '_')) {
						$sectionId = explode('_', $sectionId);
						$sectionId = $sectionId[count($sectionId)-1];
					}
					return $sectionId;
				}
				
				
				/// grab a single article by its id
				/** \param $articleId UID of the tx_newspaper_Article
				 *  \return the instantiated tx_newspaper_Article object
				 */
				function getArticleByArticleId ($articleId) {
					return new tx_newspaper_article($articleId);
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
					switch((string)$this->MOD_SETTINGS['function'])	{
						case 1:
							$content='';
							$this->content.=$this->doc->section('',$content,0,1);
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