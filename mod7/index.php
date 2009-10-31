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
 * @author	Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 * @package	TYPO3
 * @subpackage	tx_newspaper
 */
class  tx_newspaper_module7 extends t3lib_SCbase {
				var $pageinfo;

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
												
						#@todo: comment out later
						ini_set('display_errors',  'on');
						
						#@todo: make it all locallang
						#@todo: cater for backend access variations
						#@todo: clarify single-mode/needed api and implement it
						// get "pi"vars
						$input = t3lib_div::GParrayMerged('tx_newspaper_mod7');
						
						// handle ajax
						switch ($input['ajaxcontroller']) {
							case 'showplacement' :
								die($this->renderPlacement($input));
								break;
							case 'updatearticlelist' :
								die(json_encode($this->getArticleListBySectionId($input['section'], $input['articleid'])));
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
						}
						
						// instanciate smarty
						$smarty = new tx_newspaper_Smarty();
						$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod7/res/'));
						
						// get ll labels 
						$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod7/locallang.xml', $LANG->lang);
						$localLang = $localLang[$LANG->lang];
						
						// draw the header
						$this->doc = t3lib_div::makeInstance('fullWidthDoc');
						$this->doc->backPath = $BACK_PATH;
						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						
						#@todo: insert real id here
						// grab data of article to display
						$fakeArticleId = 19;
						$article = $this->getArticleByArticleId($fakeArticleId);
						$sections = $this->getSectionsByArticleId($fakeArticleId);						

						// render
						$smarty->assign('input', $input);						
						$smarty->assign('article', $article);
						$smarty->assign('sections', $sections);
						$smarty->assign('lang', $localLang);
						$html = $smarty->fetch('mod7_module.tpl');
						$this->content .= $this->doc->section('', $html, 0, 1);

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
				
				
				// place an article
				function placeArticle ($input) {
					$article = $this->getArticleByArticleId ($input['articleid']);
					$article->setAttribute('workflow_status', 2);
					$article->setAttribute('is_placed', 1);
					return true;
				}
				
				
				// send article further to chief of duty
				function sendArticleToChiefOfDuty ($input) {
					$article = $this->getArticleByArticleId ($input['articleid']);
					$article->setAttribute('workflow_status', 1);
					return true;
				}
				
				
				// send article back to editor
				function sendArticleToEditor ($input) {
					$article = $this->getArticleByArticleId ($input['articleid']);
					$article->setAttribute('workflow_status', 0);
					return true;
				}
				
				
				// set article status to online
				function putArticleOnline ($input) {
					$article = $this->getArticleByArticleId ($input['articleid']);
					$article->setAttribute('hidden', 0);
					return true;
				}
				
				
				// set article status to offline
				function putArticleOffline ($input) {
					$article = $this->getArticleByArticleId ($input['articleid']);
					$article->setAttribute('hidden', 1);
					return true;	
				}
				
				
				// save all the articles of a single section
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
							#@todo: tell helge this:
							/*
							Fatal error: Uncaught exception 'tx_newspaper_NoResException' with message 'SQL query: 
							DELETE FROM tx_newspaper_articlelist_semiautomatic_articles_mm
											WHERE
												uid IN () 
							failed with message: 
							No result set found ' in /hsphere/local/home/newspaper/dev.newspaper-typo3.org/typo3conf/ext/newspaper/classes/class.tx_newspaper.php:370
							Stack trace:
							#0 /hsphere/local/home/newspaper/dev.newspaper-typo3.org/typo3conf/ext/newspaper/classes/class.tx_newspaper_articlelist_semiautomatic.php(298): tx_newspaper::deleteRows('tx_newspaper_ar...', Array)
							#1 /hsphere/local/home/newspaper/dev.newspaper-typo3.org/typo3conf/ext/newspaper/classes/class.tx_newspaper_articlelist_semiautomatic.php(93): tx_newspaper_ArticleList_Semiautomatic->clearList()
							#2 /hsphere/local/home/newspaper/dev.newspaper-typo3.org/typo3conf/ext/newspaper/mod7/index.php(188): tx_newspaper_ArticleList_Semiautomatic->assembleFromUIDs(Array)
							#3 /hsphere/local/home/newspaper/dev.newspaper-typo3.org/typo3conf/ext/newspaper/mod7/index.php(119): tx_newspaper_module7->save in /hsphere/local/home/newspaper/dev.newspaper-typo3.org/typo3conf/ext/newspaper/classes/class.tx_newspaper.php on line 370
							 */
							$result = $section->getArticleList()->assembleFromUIDs(
								array ($articleIdsAndOffsets)  
							);
							break;
					}

					return true;
				}
				
				
				// check several article lists if they have been modified in database 
				// in comparison the the displayed ones in the form
				function checkArticleListsForUpdates ($input) {
					$input['sections'] = explode('|', $input['sections']);
					$articleLists = array();
					$result = array();
					
					// get data for all sections
					foreach ($input['sections'] as $section) {
						$articleLists[$section] = $this->getArticleListBySectionId($section, $input['placearticleuid']);
					}
					
					// do a comparison (length and order) between form input and database
					foreach ($articleLists as $articleListKey => $articleList) {
						$renderedUids = array();
						if (isset($input[$articleListKey])) {
							$renderedUids = array_values($input[$articleListKey]);
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
					}
										
					return $result;
				}
				
				
				// render the placement editors according to sections selected for article
				function renderPlacement ($input) {
					$selection = $input['sections_selected'];
					
					// calculate which / how many placers to show
					$tree = $this->calculatePlacementTreeFromSelection($selection);
					// grab the data for all the places we need to display
					$tree = $this->fillPlacementWithData($tree, $input['placearticleuid']);
					#print_r($tree);
					
					// render
					$smarty = new tx_newspaper_Smarty();
					$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod7/res/'));					
					$smarty->assign('tree', $tree);
					return $smarty->fetch('mod7_placement.tpl');
				}
				
				
				// get article and offset lists for a set of sections
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
				
				
				// extract just the article-uids from an article list
				function getArticleIdsFromArticleList ($articleList) {
					#collect all article uids
					$articleUids = array();
					foreach ($articleList as $article) {
						$articleUids[] = $article->getAttribute('uid');
					}
					return $articleUids;
				}
				
				
				// calculate a "minimal" (tree-)list of sections
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
				
				
				// get a list of articles by a section id
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
						$result['0_' . $article->getAttribute('uid')] = $article->getAttribute('title');
					}
					
					// fill the section placers from their articlelists
					foreach ($articleList as $article) {
						if ($listType == 'tx_newspaper_ArticleList_Manual') {
							$result[$article->getAttribute('uid')] = $article->getAttribute('title');
						}
						if ($listType == 'tx_newspaper_ArticleList_Semiautomatic') {
							$result[$offsetList[$article->getAttribute('uid')] . '_' . $article->getAttribute('uid')] = $article->getAttribute('title');
						}
					}
					return $result;
				}
				
				
				// grab all sections that a article can be placed in
				// semiautomatic lists get their article uid prepended with the article offset
				function getSectionsByArticleId ($articleId) {
					$result = array();
					
					$article = new tx_newspaper_article($articleId);
					$sections = $article->getSections(0);
					foreach ($sections as $section) {
						$sectionPathes =  $section->getSectionPath();
						$uids = $titles = array();
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
				
				
				// extract the section uid out of the select elements mames that are
				// like "placer_10_11_12" where we need the "12" out of it
				function extractSectionId ($sectionId) {
					if (strstr($sectionId, '_')) {
						$sectionId = explode('_', $sectionId);
						$sectionId = $sectionId[count($sectionId)-1];
					}
					return $sectionId;
				}
				
				
				// grab a single article by its id
				function getArticleByArticleId ($articleId) {
					return new tx_newspaper_article($articleId);
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