<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Stefan Fink <stefan.fink@hanse.net>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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

unset($MCONF);
require_once('conf.php');

require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');


class tx_newspaper_SectionTree extends t3lib_treeView {

	public function printTree($treeArr = '') {
		return $this->getSectionTree();
	}

	private function getSectionTree(){
		global $LANG;
		
		
		$html = '<script language="javascript">
/*<![CDATA[*/
top.currentSubScript=unescape("mod.php%3FM%3DtxnewspaperMmain_txnewspaperM3");
		///taz426/typo3/../typo3conf/ext/newspaper/mod3/class.tx_newspaper_SectionTree.php?&currentSubScript=mod.php%3FM%3DtxnewspaperMmain_txnewspaperM3
		
		// setting prefs for pagetree and drag & drop
//		Tree.highlightClass = "active";

		// Function, loading the list frame from navigation tree:
		function jumpTo(id, linkObj, highlightID, bank)	{ //
			//var theUrl = top.currentSubScript;
			var theUrl = top.TS.PATH_typo3 + top.currentSubScript ;
			if (theUrl.indexOf("?") != -1) {
				theUrl += "&id=" + id
			} else {
				theUrl += "?id=" + id		    	
			}	
			top.fsMod.currentBank = bank;

			if (top.condensedMode) {
				top.content.location.href = theUrl;
			} else {
				parent.list_frame.location.href=theUrl;
			}

			return false;
		}
/*]]>*/
</script>';
		
		
		$html .= '<b>' . $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_title_sectiontree', false) . '</b><br />';

		$pid = tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_Section());
		$row = tx_newspaper::selectRows(
			'*',
			'tx_newspaper_section',
			'pid=' . $pid,
			'',
			'section_name'
		);

		for ($i = 0; $i < sizeof($row); $i++) {
			
#<a oncontextmenu='showClickmenu("pages","6","0","","|a2a9e43201","&amp;amp;bank=0");return false;;' onclick="return jumpTo('6',this,'pages6',0);" href="#">
#<a onclick="return jumpTo('6',this,'pages6',0);" href="#">
			$aOnClick = 'return jumpTo("' . $row[$i]['uid'] . '", this, "tx_newspaper_section' .  $row[$i]['uid'] . '", 0);';
 			$html .= '<a href="#" onclick="' . htmlspecialchars($aOnClick) . '">';
			$html .= '&nbsp;' . $row[$i]['section_name'];
			$html .= '</a>';
			$html .= '<br />';	
		}
		
		$html .= '<br />';
//t3lib_div::devlog('gst row', 'newspaper', 0, array($html, $row));		
		return $html;
	}


}

class treeview_module {

	/// Initialize the page template
	function init() {
		global $BE_USER,$BACK_PATH;
//t3lib_div::devlog('mod3 st mconf', 'newspaper', 0, $GLOBALS['MCONF']);		
		// Create template object:
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->docType='xhtml_trans';

		// Setting backPath
		$this->doc->backPath = $BACK_PATH;

		$this->treeview = t3lib_div::makeInstance('tx_newspaper_SectionTree');
	}


	/// Main function, rendering the SA user tree
	function main()	{

		global $LANG,$CLIENT;

		// Start page:
		$this->content .= $this->doc->startPage('userTree');

		// add tree 
		$this->content .= $this->treeview->printTree();
	
		// Outputting refresh-link
		$refreshUrl = t3lib_div::getIndpEnv('REQUEST_URI');
		$this->content .= '
			<p class="c-refresh">
				<a href="'.htmlspecialchars($refreshUrl).'">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/refresh_n.gif','width="14" height="14"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.refresh',1).'" alt="" />'.
				'</a><a href="'.htmlspecialchars($refreshUrl).'">'.
				$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.refresh',1).'</a>
			</p>
			<br />';
	}

	/// Outputting the accumulated content to screen
	function printContent()	{
		$this->content.= $this->doc->endPage();
		echo $this->content;
	}

	////////////////////////////////////////////////////////////////////////////
	
	private $doc = null;
	private $treeview = null;
	private $content = '';
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod3/class.tx_newspaper_sectiontree.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod3/class.tx_newspaper_sectiontree.php']);
}




// Make instance:

$SOBE = t3lib_div::makeInstance('treeview_module');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();


?>