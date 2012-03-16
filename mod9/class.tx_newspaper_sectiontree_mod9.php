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


// \todo merge with mod3/class.tx_newspaper_sectiontree.php

unset($MCONF);
require_once('conf.php');

require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

require_once (PATH_t3lib.'class.t3lib_treeview.php');

/// A treeView class for displaying and browsing in tx_newspaper_Section s.
/** 
 *  \todo expand and collapse branches of sections
 */
class tx_newspaper_SectionTree_mod9 extends t3lib_treeView {
	
	/// Constructor which fully initializes the tree view
	/** I mean, come on, hey. Constructing an object, then manually assigning
	 *  all sorts of properties, then calling init() and then getTree(), as it
	 *  is done in the original \p t3lib_treeView, is just unneccessary. We'll
	 *  just do it all in the c'tor.
	 */
	public function __construct() {
		$this->init();
		$this->getTree(0);
	}
	
	/// Initialize the tree class. 
	/** Sets \p $this->table, \p $this->fieldArray, \p $this->parentField,
	 *  \p $this->expandAll and \p $this->titleAttrib
	 *  \param $clause 	record WHERE clause
	 *  \param $orderByFields record ORDER BY field
	 */
	function init($clause='', $orderByFields='')    {
        parent::init();
        $this->table = 'tx_newspaper_section';
        t3lib_div::loadTCA($this->table);
        $this->setTreeName();
        $this->parentField = 'parent_section';
        $this->fieldArray = array('uid', 'section_name');
        $this->expandAll = 1;
        $this->titleAttrib = 'section_name';
        $this->orderByFields = 'sorting';
        $this->clause = ' AND uid IN (' . implode(',', tx_newspaper_Section::getSectionTreeUids()) . ')';
	}

	/// Compiles the HTML code for displaying the structure found inside the ->tree array
	/** init() already sets up all parameters for the tree to display correctly,
	 *  we only need to supply the JavaScript jumpTo() function, which tells
	 *  the browser what to do when a node is clicked.
	 * 
	 *  \param $treeArr "tree-array" - if blank string, the internal ->tree array is used
	 */
	public function printTree($treeArr = '') {
		$ret = '<script language="javascript">
/*<![CDATA[*/
top.currentSubScript=unescape("mod.php%3FM%3DtxnewspaperMmain_txnewspaperM9");
		
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
</script>' .
		parent::printTree($treeArr);

		return $ret;
	}

	///	Returns the title for the input record.
	/** If blank, a "no title" label (localized) will be returned. Do NOT
	 *  htmlspecialchar the string from this function - has already been done.
	 * 
	 *  This function must be overridden because the field which is displayed is
	 *  hardcoded in the base class to 'title'. That's a bit braindead, and what
	 *  do you know, I use the same braindead approach here.
	 *  
	 *  \param $row The input row array (where the key "section_name" is used
	 * 		for the title)
	 *  \param $titleLen Title length (30)
	 */
	function getTitleStr($row, $titleLen=30) {
		$title = (!strcmp(trim($row['section_name']),'')) ? 
			'<em>['.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.no_title',1).']</em>' : 
			htmlspecialchars(t3lib_div::fixed_lgd_cs($row['section_name'],$titleLen));
		return $title;
	}

}

/// This class encapsulates the behavior of the old tx_newspaper_SectionTree
class treeview_module {

	/// Initialize the page template
	function init() {
		global $BE_USER,$BACK_PATH;

		// Create template object:
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->docType='xhtml_trans';

		// Setting backPath
		$this->doc->backPath = $BACK_PATH;

		$this->treeview = t3lib_div::makeInstance('tx_newspaper_SectionTree_mod9');
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
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod9/class.tx_newspaper_sectiontree.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod9/class.tx_newspaper_sectiontree.php']);
}




// Make instance:

$SOBE = t3lib_div::makeInstance('treeview_module');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();


?>