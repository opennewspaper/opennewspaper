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
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('deleteTag'),
							'2' => $LANG->getLL('mergeTags'),
//							'3' => $LANG->getLL('function3'),
						)
					);
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

					// \todo: better check: currently access to all be_users is granted
					if (isset($GLOBALS['BE_USER']->user['uid']) && $GLOBALS['BE_USER']->user['uid']) {

                        //todo: Handle Ajax if any, so content is not drawn

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
					$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod8/locallang.xml', $GLOBALS['LANG']->lang);
					$localLang = $localLang[$GLOBALS['LANG']->lang];
                    $smarty = $this->getSmarty($localLang);

                    $action = t3lib_div::GParrayMerged('action');
                    $input = t3lib_div::GParrayMerged('input');

                    switch((string)$this->MOD_SETTINGS['function'])	{
						case 1: //delete Tag
                            $content = $this->renderDeleteModule($action, $input, $smarty);
                            $section = 'sectionDelete';
						    break;
                        case 2: // merge tags
                            $content= $this->renderMergeModul($action, $input, $smarty);
                            $section = 'sectionMerge';
                            break;
                        case 3:
                            $content='<div align=center><strong>Menu item #3...</strong></div>';
                            break;
                    }
                    $this->content.=$this->doc->section($localLang[$section],$content,0,1);
                }

                          /*
                         SELECT uid_foreign
FROM `tx_newspaper_article_tags_mm` tags_mm, `tx_newspaper_article` article
WHERE tags_mm.uid_local = article.uid
AND article.deleted =0
LIMIT 0 , 30
*/
                private function renderDeleteModule($action, $input, $smarty) {
                    $tags = array();
                    $messageKey = null;
                    if(isset($action['deleteTag']) && isset($input['tag'])) {
                        $tag = new tx_newspaper_tag($input['tag']);
                        $rows = tx_newspaper::selectRows('uid_local', 'tx_newspaper_article_tags_mm', 'uid_foreign = '.$tag->getUid());
                        if(count($rows) > 0) {
                            $messageKey = 'tagInUse';
                        } else {
                            tx_newspaper::updateRows('tx_newspaper_tag', 'uid = '.$tag->getUid(), array('deleted' => 1));
                            $messageKey = 'tagDeleted';
                        }
                    } else {
                        $messageKey = 'tagSelect';
                    }

                    $tags = $this->getTagArray();

                    $smarty->assign('tags', $tags);
                    $smarty->assign('message', $messageKey);
                    return $smarty->fetch('mod8_module.tmpl');
                }

                private function renderMergeModul($action, $input, $smarty) {
                    global $LANG;
                    if(isset($action['merge']) && isset($input['tags']) && isset($input['new_tag'])) {

                    }
                    $tags = $this->getTagArray();
                    $smarty->assign('tags', $tags);
                    $smarty->assign('message', $messageKey);

                    return $smarty->fetch('mod8_mergeTags.tmpl');

                }

                private function getSmarty($localLang) {
                    $smarty = new tx_newspaper_Smarty();
                    $smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod8/res/'));
                    $smarty->assign('lang', $localLang);
                    $smarty->assign('T3PATH', tx_newspaper::getAbsolutePath(true));

                    return $smarty;
                }

                private function getTagArray() {
                    $tempTags = tx_newspaper::selectRows('uid, tag', 'tx_newspaper_tag', 'deleted = 0');
                    foreach($tempTags as $tag) {
                        $tags[$tag['uid']] = $tag['tag'];
                    }

                    return $tags;
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