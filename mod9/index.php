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


//unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH . 'init.php');
require_once($BACK_PATH . 'template.php');

require_once(PATH_typo3conf . 'ext/newspaper/Classes/private/class.tx_newspaper_placementbe.php');

/// Class to generate a BE module with 100% width
class fullWidthDoc_mod9 extends template {
	var $divClass = 'typo3-fullWidthDoc';	///< Sets width to 100%
}


$LANG->includeLLFile('EXT:newspaper/mod9/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

define('DEBUG_OUTPUT', false); // show position etc.

/**
 * Module 'Section lists' for the 'newspaper' extension.
 * 
 * This Module allows to edit the order of articles in an article list belonging
 * to a section.
 *
 * @author	Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 */
class  tx_newspaper_module9 extends t3lib_SCbase {
	var $pageinfo;
	private $be_conf = array();
	private $ll = array();
	private $prefixId = 'tx_newspaper_mod9';

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
					
					$access = $BE_USER->user['uid']? true : false; // \todo: better check needed

					if ($access)	{

						// read be conf and merge with $this->id
						$this->be_conf = unserialize($BE_USER->getModuleData('tx_newspaper/mod9'));
//t3lib_div::devlog('mod9', 'newspaper', 0, array('be_conf' => $this->be_conf, 'this->id' => $this->id));
						if (!$this->id && isset($this->be_conf['id'])) {
							$this->id = $this->be_conf['id'];
						} else {
							$this->be_conf['id'] = $this->id;
						}

						// get ll labels 
						$this->ll = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod9/locallang.xml', $GLOBALS['LANG']->lang);
						$this->ll = $this->ll[$GLOBALS['LANG']->lang];	
//t3lib_div::devlog('mod9', 'newspaper', 0, array('this->ll' => $this->ll));

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('fullWidthDoc_mod9');
						$this->doc->backPath = $BACK_PATH;
//						$this->doc->form='<form action="" method="post" enctype="multipart/form-data">';

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

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));

						// Render content:
						if (!$this->id) {
							// no section chosen
							$this->content .= $this->doc->section('', '<br /> ' . $this->ll['message_no_section_chosen'], 0, 1);
						} else {
							// render chosen section's article list
							$input = t3lib_div::GParrayMerged($this->prefixId);
							$this->moduleContent($input);
						}

						$this->content.=$this->doc->spacer(10);
						
						// store conf
						$BE_USER->pushModuleData('tx_newspaper/mod9', serialize($this->be_conf));
						
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
					$this->form = false; // do not add </form>
					$this->content .= $this->doc->endPage();
					echo $this->content;
				}

				/**
				 * Generates the module content
				 *
				 * @return	void
				 */
				function moduleContent(array $input) {
					$input['sectionid'] = $this->id;
                    $content = tx_newspaper_PlacementBE::renderSingle($input);

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



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod9/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod9/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_newspaper_module9');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>