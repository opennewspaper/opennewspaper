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
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;
					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
						//@todo: comment out later
						ini_set('display_errors',  'on');
						
						// get "pi"vars
						$input = t3lib_div::GParrayMerged($this->prefixId);
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
							case 'placearticle' :
								die($this->placeArticle($input));
							break;
							case 'sendarticletocod' :
								die($this->sendArticleToChiefOfDuty($input));
							break;
							case 'sendarticletoeditor' :
								die($this->sendArticleToEditor($input));
							break;
							case 'putarticleonline' :
								die($this->putArticleOnline($input));
							break;
							case 'putarticleoffline' :
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
						
						if (!isset($input['articleid'])) {
							$input['articleid'] = 59;
						}
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
					// get data
					$article = $this->getArticleByArticleId($input['articleid']);
					$sections = $this->getSectionsByArticleId($input['articleid']);
					$backendUser = $this->getBackendUserById($article->getAttribute('modification_user')); 	
					// get ll labels 
					$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod7/locallang.xml', $GLOBALS['LANG']->lang);
					$localLang = $localLang[$GLOBALS['LANG']->lang];					

					// instanciate smarty
					$smarty = new tx_newspaper_Smarty();
					$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod7/res/'));
					$smarty->assign('input', $input);						
					$smarty->assign('article', $article);
					$smarty->assign('sections', $sections);
					$smarty->assign('backenduser', $backendUser);
					$smarty->assign('lang', $localLang);
//					$smarty->assign('singlemode', true);
					return $smarty->fetch('mod7_module.tpl');
				}
				
				
				function userIsChiefOfDuty () {
					// @todo: use later
					// return tx_newspaper::isChief();
					return true;
				}


				function userIsEditor () {
					// @todo: use later
					// return tx_newspaper::isEditor();
					return false;
				}
				
				
				// save all the selected sections for an article
				function saveSectionsForArticle ($input) {
					$sectionIds = array();
					// we take all the sections out of the strings like 10|11|12, 10|14|17, ...
					if (is_array($input['sections_selected'])) {
						foreach ($input['sections_selected'] as $sectionCombination) {
							$sectionCombination = explode('|', $sectionCombination);
							foreach ($sectionCombination as $section) {
								if (!in_array($section, $sectionIds)) {
									$sectionIds[] = $section;
								}
							}
						}
						$article = new tx_newspaper_article($input['placearticleuid']);
						return $article->setSections($sectionIds);
					}
				}
				

				/// place an article
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function placeArticle ($input) {
					$article = $this->getArticleByArticleId ($input['placearticleuid']);
					$article->setAttribute('workflow_status', 2);
					$article->setAttribute('is_placed', 1);
					return true;
				}
				
				
				/// send article further to chief of duty
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function sendArticleToChiefOfDuty ($input) {
					$article = $this->getArticleByArticleId ($input['placearticleuid']);
					$article->setAttribute('workflow_status', 1);
					return true;
				}
				
				
				/// send article back to editor
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function sendArticleToEditor ($input) {
					$article = $this->getArticleByArticleId ($input['placearticleuid']);
					$article->setAttribute('workflow_status', 0);
					return true;
				}
				
				
				/// set article status to online
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function putArticleOnline ($input) {
					$article = $this->getArticleByArticleId ($input['placearticleuid']);
					$article->setAttribute('hidden', 0);
					return true;
				}
				
				
				/// set article status to offline
				/** \param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
				 *  \return \c true
				 */
				function putArticleOffline ($input) {
					$article = $this->getArticleByArticleId ($input['placearticleuid']);
					$article->setAttribute('hidden', 1);
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
					$smarty->assign('iscod', $this->userIsChiefOfDuty());
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
				
				
				/// grab all sections that a article can be placed in
				/** semiautomatic lists get their article uid prepended with the article offset
				 * 
				 * 	\param $articleId UID of the tx_newspaper_Article
				 *  \return \code array (
				 * 		"root_section_uid_1|...|current_section_uid_1" => "Root Section 1 > ... > Parent Section 1 > Current Section 1",
				 * 		...,
				 * 		"root_section_uid_N|...|current_section_uid_N" => "Root Section N > ... > Current Section N"
				 *  )
				 */
				 function getSectionsByArticleId ($articleId) {
					$result = array();
					
					$article = new tx_newspaper_article($articleId);
					$sections = tx_newspaper_section::getAllSections();
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