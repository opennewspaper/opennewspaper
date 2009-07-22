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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */


/// \todo:
/**
 * Inconsistency check f�r Extras:
 * alle PZs auslesen
 * dazu alle Extra auslesen und indexOfExtra() aufrufen (try catch)
 */




$LANG->includeLLFile('EXT:newspaper/mod4/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Administration' for the 'newspaper' extension.
 *
 * @author	Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 * @package	TYPO3
 * @subpackage	tx_newspaper
 */
class  tx_newspaper_module4 extends t3lib_SCbase {
				var $pageinfo;

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
							'1' => $LANG->getLL('function1'),
//							'2' => $LANG->getLL('function2'),
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

					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;
				
					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('bigDoc');
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

						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->section('',$this->doc->funcMenu('', t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


						// ShortCut
						if ($BE_USER->mayMakeShortcut())	{
							$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
						}

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
					switch((string)$this->MOD_SETTINGS['function'])	{
						case 1:
							$content = '
								The database is checked for inconsistent data.
								<hr />';
//								GET:'.t3lib_div::view_array($_GET).'<br />'.
//								'POST:'.t3lib_div::view_array($_POST).'<br />'.
								
								
							$f = $this->getListOfDbConsistencyChecks();
							for ($i = 0; $i < sizeof($f); $i++) {
								$content .= '<br /><b>' . $f[$i]['title'] . '</b><br />';
								$tmp = call_user_func_array($f[$i]['class_function'], $f[$i]['param']);
								if ($tmp === true) {
									$content .= 'No problems found<br />';
								} else {
									$content .= $tmp;
								}
							}

								
							$this->content .= $this->doc->section('Newspaper: db consistency check', $content, 0, 1);
						break;
						case 2:
							$content='<div align=center><strong>Menu item #2...</strong></div>';
							$this->content.=$this->doc->section('Message #2:',$content,0,1);
						break;
						case 3:
							$content='<div align=center><strong>Menu item #3...</strong></div>';
							$this->content.=$this->doc->section('Message #3:',$content,0,1);
						break;
					}
				}


	
	function getListOfDbConsistencyChecks() {
		$f = array(
			array(
				'title' => 'Abstract extra: concrete extra missing',
				'class_function' => array('tx_newspaper_module4', 'checkAbstractExtraConcreteExtraMissing'),
				'param' => array()
			),
			array(
				'title' => 'Section: multiple pages with same page type for a section',
				'class_function' => array('tx_newspaper_module4', 'checkSectionWithMultipleButSamePageType'),
				'param' => array()
			),
			array(
				'title' => 'Extra in Article: article or pagezone set as Extra',
				'class_function' => array('tx_newspaper_module4', 'checkExtraInArticleIsArticleOrPagezone'),
				'param' => array()
			),
			array(
				'title' => 'Free Extras: Extras which belong to no PageZone or Article',
				'class_function' => array('tx_newspaper_module4', 'checkFreeExtras'),
				'param' => array()
			),
		);
		return $f;
	}
	
	static function checkSectionWithMultipleButSamePageType() {
		$msg = '';
		$GLOBALS['TYPO3_DB']->debugOutput = true;
//		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
//			'section, pagetype_id, deleted, count(*) AS c',
//			'tx_newspaper_page',
//			'',
//			'section, pagetype_id, deleted',
//			'(c>1 AND deleted=0)'
//		);
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT section, pagetype_id, deleted, count(*) AS c FROM tx_newspaper_page GROUP BY section, pagetype_id,deleted HAVING (c>1 AND deleted=0)');
		if (!$res)
			die('Could not read table tx_newspaper_page');
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        	$msg .= 'Section uid ' . $row['section'] . ' has ' . $row['c'] . ' pages of page type uid ' . $row['pagetype_id'] . '<br />';
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

		if ($msg != '')
			return $msg;
		return true;
	}
	
	/// searches abstract extras where the related concrete extra is missing or deleted
	static function checkAbstractExtraConcreteExtraMissing() {
		$msg = '';
		// get all concrete extra table where records should exist
		$abstract_extra_type_row = tx_newspaper::selectRows(
			'DISTINCT extra_table',
			'tx_newspaper_extra'
		);
		for($i = 0; $i < sizeof($abstract_extra_type_row); $i++) {
//t3lib_div::debug($abstract_extra_type_row[$i]['extra_table']);

			// get all concrete uid for this extra (from abstract table)
			$abstract_row = array();
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid,extra_uid',
				'tx_newspaper_extra',
				'deleted=0 AND extra_table="' . $abstract_extra_type_row[$i]['extra_table'] . '"'
			);
			if (!$res)
				die('Could not read extra abstract rows for table ' . $abstract_extra_type_row[$i]['extra_table']);
	        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	        	$abstract_row[$row['extra_uid']] = $row['uid']; // key = uid of concrete extra, value = uid of abstract extra
	        }
	        $GLOBALS['TYPO3_DB']->sql_free_result($res);
//t3lib_div::debug($abstract_row);

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid, deleted',
				$abstract_extra_type_row[$i]['extra_table'],
				'1'
			);
			if (!$res)
				die('Could not read extra concrete rows for extra ' . $row[$i]['extra_table']);
	        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	        	if (isset($abstract_row[$row['uid']])) {
	        		// so an abstract extra exsits for this concrete extra (it's ok if no abstract record is available)
	        		unset($abstract_row[$row['uid']]); // concrete extra for this abstract extra found, it's deleted, so only inconsistent records will remain
	        	}
	        }
	        $GLOBALS['TYPO3_DB']->sql_free_result($res);


			if (sizeof($abstract_row) > 0) {
				if ($msg != '')
					$msg .= '<br /><br >';
				$msg = 'Problem(s) found for table ' . $abstract_extra_type_row[$i]['extra_table'] . ':<br />';
				foreach($abstract_row as $key => $value) {
					$msg .= 'Abstract record uid ' . $value . ' is linked to non-existing concrete uid ' . $key . '<br />'; 
				}
			}
			
			
			
		}
		
		if ($msg != '')
			return $msg;
		return true; // no problems found
	}
	
	

	/// searches abstract extras where the related concrete extra is missing or deleted
	static function checkExtraInArticleIsArticleOrPagezone() {
		$msg = '';
		// get all concrete extra table where records should exist
		
		$row = tx_newspaper::selectRows(
			'*',
			'tx_newspaper_article_extras_mm mm, tx_newspaper_extra e',
			'mm.uid_foreign=e.uid AND (e.extra_table="tx_newspaper_pagezone_page" OR e.extra_table="tx_newspaper_article" OR e.extra_table="tx_newspaper_pagezone")'
		);
		
		$msg = '';
		for($i = 0; $i < sizeof($row); $i++) {
			$msg .= 'Article #' . $row[$i]['uid_local'] . ', abstract Extra #' . $row[$i]['uid_foreign'] . 
				' is stored in table ' . $row[$i]['extra_table'] . ' with #' . $row[$i]['extra_uid'] . '<br />';
		}
		
		if ($msg != '')
			return $msg;
		return true; // no problems found
	}

	/// searches for extras which don't belong to either a pagezone or an article
	static function checkFreeExtras() {		
		$row = tx_newspaper::selectRows(
			'*',
			'tx_newspaper_extra',
			'NOT uid in (SELECT uid_foreign FROM `tx_newspaper_pagezone_page_extras_mm`) 
			 AND NOT uid in (SELECT uid_foreign FROM `tx_newspaper_article_extras_mm`) 
			 AND NOT deleted',
			 '', 'uid'
		);
		
		$msg = '';
		for($i = 0; $i < sizeof($row); $i++) {
/*			$concrete = tx_newspaper::selectOneRow(
				'*', $row[$i]['extra_table'],
				'uid = ' . $row[$i]['extra_uid']
			);
*/			$msg .= 'Extra #' . $row[$i]['uid'] . '(concrete: ' . $row[$i]['extra_table'] . 
					' #' . $row[$i]['extra_uid'] . ')'. 
					' is not connected to either an article or a page zone.<br />';
		}
		
		if ($msg != '')
			return $msg;
		return true; // no problems found
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod4/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod4/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_newspaper_module4');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>