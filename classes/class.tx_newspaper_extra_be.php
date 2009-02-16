<?php
/**
 *  \file class.tx_newspaper_extra_be.php
 *
 *  \author Oliver Schröder <newspaper@schroederbros.de>
 *  \date Dec 12, 2008
 */

define('EXTRA_DISPLAY_MODE_MODAL', 'modalbox');
define('EXTRA_DISPLAY_MODE_IFRAME', 'iframe');

/**
 * The Extra form can be displayed in iframe or modalbox mode (other modes can be added by xclassing)
 * All BE modifications needed to implement a mode are bundled in this class
 */
class tx_newspaper_ExtraBE {

	private static $be_mode = null; /// < stores the backend mode (iframe, modalbox)


/// \to do: called by tx_newspaper->renderList() - is class tx_newspaper still needed? (t3 naming convention for user field?)
	/// renders the list of associates Extra
	/** \param $table table Extras are associates with (f. ex. tx_newspaper_article)
	 *  \param $uid uid of record in given table
	 *  \return String html code with list of Extras
	 */
	public static function renderList($table, $uid) {
		
		$listOfExtras = self::readExtraList($table, $uid);
#t3lib_div::devlog('renderList', 'newspaper', 0, $listOfExtras);
		
		$content = '';
		for ($i = 0; $i < sizeof($listOfExtras); $i++) {
			$content .= self::renderListItem($listOfExtras[$i]);
		}	
		
		if ($content) {
			$content = tx_newspaper_ExtraBE::getJsForExtraForm() . $content; // add javscript for display mode (especially getExtra code)
			$content = '<table>' . $content . '</table>'; // wrap in table
		}

		return $content;
	}
	
	
	/// read list of associated Extras from database
	/** \param $table local table (tx_newspaper_article) 
	 *  \param $uid local uid in given table
	 *  \return Array entries in tx_newspaper_extra associated with current article
	 */
	private static function readExtraList($table, $uid) {
		
#$GLOBALS['TYPO3_DB']->debugOutput = true;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'extra_table, extra_uid, paragraph, position',
			'tx_newspaper_article',
			'tx_newspaper_article_extras_mm',
			'tx_newspaper_extra',
			' AND uid_local=' . $uid,
			'',
			'paragraph, position'
		); 
#t3lib_div::devlog('readExtraList - query', 'newspaper', 0, $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);
		$list = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$class = $row['extra_table'];
			if (class_exists($class)) {
				if ($row['extra'] = call_user_func_array(array($class, 'readExtraItem'), array($row['extra_uid'], $class))) {
					// append data for this extra (if found and accessible)
					$row['type'] = call_user_func_array(array($class, 'getTitle'), array()); // get title of this extra and add to array
					$list[] = $row;
				} // \to do:  else throw Exception
			} // \to do: else throw Exception Extra class not found
		}
		
		return $list;
	}


	/// render one item (=row) in list of Extras
	/** \param $item data for that Extra (row from database)
	 *  \return String html code of that row
	 */
	private static function renderListItem(array $item) {
		global $LANG;
t3lib_div::devlog('renderListItem item', 'newspaper', 0, $item);
		$id = $item['extra_type'] . '[' . $item['uid_foreign'] . ']' . 
			$item['tablenames'] . '[' . $item['uid_local'] . ']';

		$content_table = $item['tablenames'];
		$content_uid = $item['uid_local'];

		/// build links for BE
		$ahref = array(); 
		$ahref['edit'] .= '<a href="javascript:getExtra(\'' . 
			$item['extra_table'] .'\', ' . $item['extra_uid'] .', \'' . 
			$content_table . '\', ' .$content_uid . ');">';
		$ahref['toggle_visibility'] = 
			'<a href="javascript:toggleExtraVisibility(\'' . 
			$item['extra_table'] .'\', ' . $item['extra_uid'] .', \'' . 
			$content_table . '\', ' .$content_uid . ', $(\'vis_icon_' . $id . 
			'\').src);">';
		$ahref['delete'] = '<a href="javascript:deleteExtra(\'' . 
			$item['extra_table'] .'\', ' . $item['extra_uid'] .', \'' . 
			$content_table . '\', ' . $content_uid . ', false);">';

		$content = '<tr id="list_' . $id . '">';
		$content .= '<td bgcolor="white">' . $item['type'] . '</td>';
		$content .= '<td id="title_' . $id . '">' . $item['extra']['title'] . '</td>';
//$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.showPage', 1)
		// edit
		$content .= '<td>' . self::renderIcon('gfx/edit2.gif', '', 
			$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.php:flag.extra_edit', false), 
			$ahref['edit'], true) . '</td>';

		// visibility
		if (!$item['extra']['hidden']) {
			$content .= '<td id="visibility_' . $id . '">' . 
				self::renderIcon('gfx/button_hide.gif', 'vis_icon_' . $id, 
				$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.php:flag.extra_hide', false), 
				$ahref['toggle_visibility'], false) . '</td>';
		} else {
			$content .= '<td id="visibility_' . $id . '">' . 
				self::renderIcon('gfx/button_unhide.gif', 'vis_icon_' . $id, 
				$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.php:flag.extra_unhide', false), 
				$ahref['toggle_visibility'], false) . '</td>';
		}

		// delete
		$content .= '<td>' . 
			self::renderIcon('gfx/garbage.gif', '', 
			$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.php:flag.extra_delete', false), 
			$ahref['delete'], true) . '</td>';

		if (self::getExtraBeDisplayMode() == EXTRA_DISPLAY_MODE_IFRAME) {
// \to do: colspan still constant
			/// insert row for iframe (one per Extra)
			$content .= '<tr id="' . $id . 
				'" style="display: block;"><td colspan="5"></td></tr>';
		}
		
		return $content;
	}
	
	
	/// get html for this icon (may include an anchor) 
	/** \param $image path to icon
	 *  \param $id 
	 *  \param $title title for title flag of img
	 *  \param $ahref 
	 *  \param $replaceWithCleargifIfEmpty if set to true the icon is replaced with clear.gif, if $ahref is empty
	 *  \return String <img ...> or <a href><img ...></a> (if linked)
	 */
	private static function renderIcon($image, $id, $title='', $ahref='', $replaceWithCleargifIfEmpty=false) {

/// \to do: read width and height from file? or hardcode 16x16px?
		$width = 16;
		$height = 16;
		
		if ($id)
			$id = ' id="' . $id . '" '; // if id is set, set build correct attribute id="..."

		if ($ahref == '' && $replaceWithCleargifIfEmpty) {
			// hide icon (= replace with clear.gif)
			$html = '<img' . $id . t3lib_iconWorks::skinImg('', 'clear.gif', 'width="' . $width . '" height="' . $height . '"') . ' title="' . $title . '" alt="" />';
		} else {
			// show icon
			$html = '<img' . $id . t3lib_iconWorks::skinImg('', $image, 'width="' . $width . '" height="' . $height . '"') . ' title="' . $title . '" alt="" />';
		}
		if ($ahref)
			return $ahref . $html . '</a>'; // if linked wrap in link
		return $html; // return image html code

	}





// the methods below are used to render different display modes (iframe, modalbox)

	/**
	 * returns the mode in which the Extra form is displayed (modalbox or iframe)
	 * (set as Page TSConfig tx_newspaper.extra_mode = modalbox|iframe or as User TSConfig page.tx_newspaper.extra_mode = ...)
	 * \return String const value for display mode (modalbox is default)
	 */
	public static function getExtraBeDisplayMode() {

		if (self::$be_mode)
			return self::$be_mode; // be_mode already known

		$value = '';
		if (isset($GLOBALS['BE_USER']->userTS['tx_newspaper.']['extra_mode'])) {
			/// user tsconfig has higher priority than page tsconfig
#t3lib_div::devlog('user ts', 'newspaper', 0, $GLOBALS['BE_USER']->userTS['tx_newspaper.']);
			$value = $GLOBALS['BE_USER']->userTS['tx_newspaper.']['extra_mode'];
		}

		if (!$value) {
			/// check page tsconfig, if no use tsconfig was found
			$sf = tx_newspaper_Sysfolder::getInstance();
			$tsconfig = t3lib_BEfunc::getPagesTSconfig($sf->getPidRootfolder());
#t3lib_div::devlog('page tsc', 'newspaper', 0, $tsconfig);
			if (isset($tsconfig['tx_newspaper.']['extra_mode'])) {
				/// read tsconfig for Extra data		;
				$value = $tsconfig['tx_newspaper.']['extra_mode'];
			}
		} 

		$mode = EXTRA_DISPLAY_MODE_IFRAME; ///< set default
		if ($value) {
			switch(trim(strtolower($value))) {
				case EXTRA_DISPLAY_MODE_MODAL:
					$mode = EXTRA_DISPLAY_MODE_MODAL;
				break;
				case EXTRA_DISPLAY_MODE_IFRAME:
				default:
					$mode = EXTRA_DISPLAY_MODE_IFRAME;
				break;
				// other display modes can be added here (f. ex. another modal box script)
				// those modes has to be defined (see top of this file) and integrated in all get[...]() and add[...] methods below
				// additional scripts are to be added in a sub directory of the res diretory
			}
		}
#t3lib_div::devlog('getExtraBeDisplayMode', 'newspaper', 0, $mode);

		self::$be_mode = $mode; ///< store be_mode, so next access won't read tsconfig from database

		return $mode;
	}

	/**
	 * return html code to include a js file to handle the AJAX call to open the Extra form (added to extra_field)
	 * \return html code <script src="..."> </script>
	 */
	public static function getJsForExtraForm() {
		switch(self::getExtraBeDisplayMode()) {
			case EXTRA_DISPLAY_MODE_IFRAME:
				$js = '<script language="javascript" type="text/javascript" src="' . t3lib_extMgm::extRelPath('newspaper') . 'res/extra_iframe.js"> </script>';
			break;
			case EXTRA_DISPLAY_MODE_MODAL:
				$js = '<script language="javascript" type="text/javascript" src="' . t3lib_extMgm::extRelPath('newspaper') . 'res/extra_modalbox.js"> </script>';
			break;
		}
		return $js;
	}

	/**
	 * return html code to include a js file to handle the Extra form
	 * (usually used to add an onunload script to update the Extra in the parent form)
	 * \return html code <script src="..."> </script>
	 */
	public static function getJsForExtraField() {
#t3lib_div::devlog('getJsForExtraField', 'newspaper', 0);
		switch(self::getExtraBeDisplayMode()) {
			case EXTRA_DISPLAY_MODE_IFRAME:
				$js = '<script type="text/javascript" src="' . t3lib_extMgm::extRelPath('newspaper') . 'res/extra_form_iframe.js"></script>';
			break;
			case EXTRA_DISPLAY_MODE_MODAL:
				$js = '<script type="text/javascript" src="' . t3lib_extMgm::extRelPath('newspaper') . 'res/extra_form_modalbox.js"></script>';
			break;
		}
		return $js;
	}


	/**
	 * add javascript and css files needed for display mode (adds to $GLOBALS['TYPO3backend'])
	 * uses this hook: $GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][] = ...
	 * \return true, if files were added
	 */
	function addAdditionalScriptToBackend() {
		$files_added = false;
		switch(self::getExtraBeDisplayMode()) {
			case EXTRA_DISPLAY_MODE_IFRAME:
				// nothing to add for iframe mode
			break;
			case EXTRA_DISPLAY_MODE_MODAL:
				// add modalbox js to top (so modal box can be displayed over the whole backend, not only the content frame)
				$GLOBALS['TYPO3backend']->addJavascriptFile(t3lib_extMgm::extRelPath('newspaper') . 'contrib/subModal/common.js');
				$GLOBALS['TYPO3backend']->addJavascriptFile(t3lib_extMgm::extRelPath('newspaper') . 'contrib/subModal/subModal.js');
				$GLOBALS['TYPO3backend']->addCssFile('subModal', t3lib_extMgm::extRelPath('newspaper') . 'contrib/subModal/subModal.css');
				$files_added = true;
			break;
		}
		return $files_added;
	}


}
/// \to do: xclassing still not available, so new modes can't be added with an XCLASS

?>