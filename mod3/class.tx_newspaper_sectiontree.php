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

	function __toString() {
		return 
" table = $this->table
 parentField = $this->parentField
 clause = $this->clause
 orderByFields = $this->orderByFields
 fieldArray = " . print_r($this->fieldArray, 1) ."
 defaultList =" . print_r($this->defaultList, 1) ."
 treeName = $this->treeName
 data = $this->data
 dataLookup = $this->dataLookup
 tree = " . print_r($this->tree, 1);
	}
	
	/// Initialize the tree class. Will set ->fieldsArray, ->backPath and ->clause
	/** \param $clause 	record WHERE clause
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
#        t3lib_div::devlog('tx_newspaper_SectionTree::init()', 'newspaper', 0, $this->__toString()); 
	}
 
	/// Compiles the HTML code for displaying the structure found inside the ->tree array
	/** \param $treeArr "tree-array" - if blank string, the internal ->tree array is used
	 *  \todo make this function conform to \p t3lib_treeView standards
	 */
	public function printTree($treeArr = '') {
		$ret = '<script language="javascript">
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
</script>' .
		parent::printTree($treeArr);
        t3lib_div::devlog('tx_newspaper_SectionTree::printTree()', 'newspaper', 0, $this->__toString()); 
		return $ret;
#		return $this->getSectionTree();
	}

	function getTitleStr($row, $titleLen=30) {
		$title = (!strcmp(trim($row['section_name']),'')) ? 
			'<em>['.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.no_title',1).']</em>' : 
			htmlspecialchars(t3lib_div::fixed_lgd_cs($row['section_name'],$titleLen));
		return $title;
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
		$this->treeview->init();
	}


	/// Main function, rendering the SA user tree
	function main()	{

		global $LANG,$CLIENT;

		// Start page:
		$this->content .= $this->doc->startPage('userTree');

		// add tree 
		$this->treeview->getTree(0);
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