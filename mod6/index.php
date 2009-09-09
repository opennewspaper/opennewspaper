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

// width: 100%
class fullWidthDoc extends template {
	var $divClass = 'typo3-fullWidthDoc';
}


/**
 * Module 'Control Tag Control' for the 'newspaper' extension.
 *
 * \author	Helge Preuss <helge.preuss@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_newspaper
 */
class  tx_newspaper_module6 extends t3lib_SCbase {
	
	var $pageinfo;

	private $smarty = null;				///< tx_newspaper_Smarty rendering engine
	
	///	Table mapping tx_newspaper_Extra s to tag zones and tx_newspaper_Tag s
	const controltag_to_extra_table = 'tx_newspaper_controltag_to_extra';
	
	///	Table listing the possible tag zones
	const tag_zone_table = 'tx_newspaper_tag_zone';

	///	Table listing the possible tags
	const tag_table = 'tx_newspaper_tag';
	
	///	Fields in the controltag_to_extra_table not to display (system fields)
	private static $excluded_fields = array(
		'uid', 'pid', 'tstamp', 'crdate', 'cruser_id'
	);
	
	private static $writable_fields = array(
		'tag', 'tag_zone', 'extra_table', 'extra_uid'
	)


	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		$this->smarty = new tx_newspaper_Smarty();
		$this->smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod6/'));
		
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
				'2' => $LANG->getLL('function2'),
				'3' => $LANG->getLL('function3'),
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

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
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
	function moduleContent()	{
		global $LANG;
		if (!($LANG instanceof language)) {
			require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
			$LANG = t3lib_div::makeInstance('language');
			$LANG->init('default');
		}
		
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$tag_zones = tx_newspaper::selectRows(
					'uid, name', self::tag_zone_table
				);
				$this->smarty->assign('tag_zones', $tag_zones);

				$tags = tx_newspaper::selectRows(
					'uid, tag', self::tag_table,
					'tag_type = \'' . self::getControlTagType() . '\''
				);
				$this->smarty->assign('tags', $tags);				

				$extra_types = array();
				foreach (tx_newspaper_Extra::getRegisteredExtras() as $registered_extra) {
					$extra_types[] = array(
						'table' => $registered_extra->getTable(), 
						'title' =>$registered_extra->getTitle()
					);
				}
				$this->smarty->assign('extra_types', $extra_types);				
				
				$data = tx_newspaper::selectRows(
					'*', self::controltag_to_extra_table
				);
				
				if ($data) {
					foreach ($data as $index => $row) {
						$tag = tx_newspaper::selectOneRow(
							'tag', 'tx_newspaper_tag', 'uid = ' . $row['tag']
						);
						$data[$index]['tag'] = $tag['tag'];
						$tag_zone = tx_newspaper::selectOneRow(
							'name', 'tx_newspaper_tag_zone', 'uid = ' . $row['tag_zone']
						);
						$data[$index]['tag_zone'] = $tag_zone['name'];
						
						$extra = new $row['extra_table']($row['extra_uid']);
						$data[$index]['extra_uid'] = $extra->getDescription();
					}
					$this->smarty->assign('data', $data);
							
					$this->content .= $this->doc->section(
						'Message #1:', 
						$this->smarty->fetch('mod6.tmpl'),
						0, 1
					);
				}
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
		if ($_POST) {
			foreach ($_POST as $field => $rows) {
				if (in_array($field, self::$writable_fields)) {
					$this->content .= "<p><strong>$field</strong>" . print_r($rows, 1) . "</p>";
				}
			}
		}
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	static private function getControlTagType() {
		return 'control';
	}
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