<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
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
require_once('../classes/class.tx_newspaper_tag.php');

$LANG->includeLLFile('EXT:newspaper/mod8/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]


/// Class to generate a BE module with 100% width
class fullWidthDoc extends template {
	var $divClass = 'typo3-fullWidthDoc';	///< Sets width to 100%
}

/**
 * Module 'Tag' for the 'newspaper' extension.
 *
 * @author	Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 * @package	TYPO3
 * @subpackage	tx_newspaper
 */
class  tx_newspaper_module8 extends t3lib_SCbase {

	const prefixId = 'tx_newspaper_mod8';

	// these attributes are filled in init();
	private $localLang; // localized labels etc.
	private $input=array(); // store get params (based on $this->prefixId)


				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					parent::init();

					// localized strings
					$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod8/locallang.xml', $GLOBALS['LANG']->lang);

					// localized string for mod8
					$this->localLang = $localLang[$GLOBALS['LANG']->lang];

					// add some other localized strings
					$this->localLang['npContentTag'] = $LANG->sL('LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag.tag_type.I.0', false);
					$this->localLang['npCtrlTag'] = $LANG->sL('LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag.tag_type.I.1', false);


                    $this->input = t3lib_div::GParrayMerged('$this->prefixId'); // get params

				}

				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 * @return	void
				 */
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'3' => $LANG->getLL('renameTags'),
							'2' => $LANG->getLL('mergeTags'),
							'1' => $LANG->getLL('deleteTag'),
						)
					);
					parent::menuConfig();
				}

				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					// \todo: better check: currently access to all be_users is granted
					if (isset($GLOBALS['BE_USER']->user['uid']) && $GLOBALS['BE_USER']->user['uid']) {

						$this->input = t3lib_div::GParrayMerged(self::prefixId); // store params
//t3lib_div::devlog('Tag module', 'newspaper', 0, array('input' => $this->input));

						$this->handleAjax(); // ends with die() if and ajax request is processed


							// Draw the header.
						$this->doc = t3lib_div::makeInstance('fullWidthDoc');
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

						$headerSection = "";

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


						// ShortCut
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
				 * @return	void
				 */
				function printContent()	{

					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				/**
				 * Generates the module content
				 */
				function moduleContent()	{
					$content = $this->renderTagTypeSelection();
					$smarty = $this->getSmarty();
					switch((string)$this->MOD_SETTINGS['function'])	{
						case 1: //delete tag
							$content .= $smarty->fetch('mod8_deleteTag.tmpl');
							$title = 'deleteTags';
						break;
						case 2: // merge tags
							$content .= $smarty->fetch('mod8_mergeTags.tmpl');
							$title = 'mergeTags';
							break;
						case 3:
							$smarty->assign('ICON', array(
								'undo' => tx_newspaper_BE::renderIcon('gfx/undo.gif', ''),
								'save' => tx_newspaper_BE::renderIcon('gfx/saveandclosedok.gif', '')
							));
							$content .= $smarty->fetch('mod8_renameTag.tmpl');
							$title = 'renameTags';
						break;
                    }
                    $this->content .= $this->doc->section($this->localLang[$title], $content, 0, 1);
                }


				/**
				 * Handle AJAX requests in tag module
				 */
                private function handleAjax() {
                	switch ($this->input['ajaxController']) {
                		case 'changeTagCat':
							$this->ajaxChangeTagCat();
						break;
						case 'mergeTags':
							$this->ajaxMergeTags();
						break;
						case 'renameTag':
							$this->ajaxRenameTag();
						break;
						case 'deleteTag':
							$this->ajaxDeleteTag();
						break;
                	}
                }


                private function ajaxDeleteTag() {
					$tag = new tx_newspaper_tag(intval($this->input['tagUid']));
					if ($tag->getArticles(1)) {
						if ($this->input['confirmDetachTags']) {
							$tag->detach();
						} else {
							die(json_encode(array('success' => false, 'attachedTagsFound' => true)));
						}
					}
					$tag->delete();
					die(json_encode(array('success' => true)));
                }


                private function ajaxRenameTag() {
					$tag = new tx_newspaper_tag(intval($this->input['tagUid']));
                	if (!$tag->isTagUnique($this->input['newTagName'])) {
						die(json_encode(array('success' => false)));
					}
					$tag->storeRenamedTag($this->input['newTagName']);
					die(json_encode(array('success' => true)));
                }


                private function ajaxMergeTags() {
					// extract source tag uids
                	$sourceTagUids = explode('|', $this->input['sourceTags']);

                	// create target tag
                	$targetTag = new tx_newspaper_tag(intval($this->input['targetTag']));

                	// merge source tags
                	$mergeCount = 0;
                	foreach($sourceTagUids as $sourceTagUid) {
                		if ($targetTag->merge(new tx_newspaper_tag($sourceTagUid))) {
                			$mergeCount++;
                		}
                	}

                	if ($mergeCount) {
						die($this->localLang['mergeSuccess'] . $mergeCount);
                	} else {
						die($this->localLang['mergeFailed']);
                	}

                }

                /**
                 * Fetches all tags requested in $this->input
                 * Dies with JSON array: tagUid|title
                 */
                private function ajaxChangeTagCat() {
	                if ($this->input['tagType'] == 'content') {
						$tags = tx_newspaper_tag::getAllContentTags();
	                } elseif ($this->input['tagType'] == 'ctrl' && $this->input['ctrlTagCat']) {
						$tags = tx_newspaper_tag::getAllControlTags(intval($this->input['ctrlTagCat']));
	                } else {
	                	t3lib_div::devlog('Tag module: unknown tagType', 'newspaper', 3, array('input' => $this->input));
	                	die();
	                }
					$options = array();
					foreach($tags as $tag) {
						$options[] = $tag->getUid() . '|' . htmlspecialchars($tag->getAttribute('tag'));
					}
					die(json_encode($options));
               	}


               	/**
               	 * Renders upper part of backend: selection of tag type (and control tag categorie, if tag type is control tag)
               	 * Tags are fetched via AJAX request
               	 */
                private function renderTagTypeSelection() {
					$smarty = $this->getSmarty();
					$smarty->assign('CTRLTAGCATS', tx_newspaper_tag::getAllControltagCategories());
					return $smarty->fetch('mod8_base.tmpl');
                }

                /// \return Smarty objects with T3PATH and LL (localized strings) assigned already
                private function getSmarty() {
                    $smarty = new tx_newspaper_Smarty();
                    $smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod8/res/'));
                    $smarty->assign('LL', $this->localLang);
                    $smarty->assign('T3PATH', tx_newspaper::getAbsolutePath(true));
                    return $smarty;
                }

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod8/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod8/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_newspaper_module8');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>