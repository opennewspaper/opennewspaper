<?php

define('EXTRA_DISPLAY_MODE_MODAL', 1); // default value for display mode
define('EXTRA_DISPLAY_MODE_IFRAME', 2);

class Extra {

	private static $registeredExtra = array(); // list of registered Extras


	/**
	 * get uid of folder used to store data
	 * \param String $extra Extra to get pid of
	 * \ return int pid of Folder to store data for given Extra
	 */
	public static function getExtraPid() {
//TODO: set REAL page_uid (where does that come from? config????)
//TODO: should be done like dam does it: Extra folder can be hidden (if not existing the folder is created)
//TODO: one folder per Extra or one folder for ALL Extras?
		return 7;
	}


	/**
	 * checks if backend is in modal box mode (or iframe mode)
	 * (set as Page TSConfig tx_extra.mode = modal|iframe or as User TSConfig page.tx_extra.mode = ...)
	 * \param String $extra name of Extra to get display mode
	 * \return int const value for display mode
	 */
	public static function getDisplayMode() {
//TODO: move all switch statements here?
		// read tsconfig of folder for Extra data
		$tsconfig = t3lib_BEfunc::getPagesTSconfig(self::getExtraPid());

		if (isset($tsconfig['tx_extra.']['mode']) && trim(strtolower($tsconfig['tx_extra.']['mode'])) == 'iframe')
			return EXTRA_DISPLAY_MODE_IFRAME; // mode is set to iframe

		// other display modes may be added here (f. ex. another modal box script)
		// those modes has to be defined (see top of this file) and integrated in all switch(getDisplayMode()) statements

		return EXTRA_DISPLAY_MODE_MODAL; // use default mode: modal

	}


	/**
	 * every Extra has to register to be used
	 * \param String $title title of the extra (database table)
	 * \return true if this Extra was registered or false if Extra was registered already
	 */
	public static function registerExtra($title) {
		if (!self::isRegisteredExtra($title)) {
			self::$registeredExtra[] = $title; // add this Extra to list
			return true;
		}
		return false;
	}

	/**
	 * checks if an Extra is registered
	 * \return true if the Extra is registered (else false)
	 */
	public static function isRegisteredExtra($title) {
		return in_array($title, self::$registeredExtra);
	}


	function renderList($table, $uid) {
		global $LANG;

		$listOfExtras = Extra::readList($table, $uid);

		$content = '';

		// add javscript for display mode
		switch(self::getDisplayMode()) {
			case EXTRA_DISPLAY_MODE_IFRAME:
				$content .= '<script language="javascript" type="text/javascript" src="' . t3lib_extMgm::extRelPath('extra') . 'res/extra_iframe.js"> </script>';
			break;
			case EXTRA_DISPLAY_MODE_MODAL:
			default:
				$content .= '<script language="javascript" type="text/javascript" src="' . t3lib_extMgm::extRelPath('extra') . 'res/extra_modalbox.js"> </script>';
			break;
		}

//TODO: check permissions before setting buttons (especially delete button)
//TODO: labels (LLL)
		for ($i = 0; $i < sizeof($listOfExtras); $i++) {
			$id = $listOfExtras[$i]['extra_type'] . '[' . $listOfExtras[$i]['ref_extra'] . ']' .
					$listOfExtras[$i]['content_table'] . '[' . $listOfExtras[$i]['ref_content'] . ']';
			$type = $listOfExtras[$i]['extra_type'];
			$extra_uid = $listOfExtras[$i]['ref_extra'];
			$content_table = $listOfExtras[$i]['content_table'];
			$content_uid = $listOfExtras[$i]['ref_content'];

//TODO: most real links still missing
			// init links
			$ahref['delete'] = '';
			$ahref['edit'] = '';
			$ahref['toggle_visibility'] = '';
			$ahref['preview'] = '';
			// check permissions (and add a hrefs if permissions are granted)
			if (self::isUserAllowedToEdit()) {
				$ahref['edit'] .= '<a href="javascript:getExtra(\'' . $type .'\', ' . $extra_uid .', \'' . $content_table . '\', ' .$content_uid . ');">';
				$ahref['toggle_visibility'] = '<a href="#">';
			}
			if (self::isUserAllowedToDelete()) {
				$ahref['delete'] = '<a href="#">';
			}
			if (self::isUserAllowedToPreview()) {
				$ahref['preview'] = '<a href="#">';
			}
			if (self::isUserAllowedToViewInfo()) {
				$ahref['info'] = '<a href="#">';
			}


			$content .= '<tr>';
			$content .= '<td bgcolor="white">' . $listOfExtras[$i]['type'] . '</td>';
			$content .= '<td id="title_' . $id . '">' . $listOfExtras[$i]['extra']['title'] . '</td>';

			// preview
			$content .= '<td>' . self::renderIcon($this->backPath, 'gfx/zoom.gif', $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.showPage', 1), $ahref['preview'], true) . '</td>';

			// edit
			$content .= '<td>' . self::renderIcon($this->backPath, 'gfx/edit2.gif', $LANG->getLL('editPage', 1), $ahref['edit'], true) . '</td>';

			// info
			$content .= '<td><img' . self::renderIcon($this->backPath, 'gfx/zoom2.gif', $LANG->getLL('showInfo',1), $ahref['info'], true) . '</td>';

			// visibility
			if (!$listOfExtras[$i]['extra']['hidden']) {
				$content .= '<td id="visibility_' . $id . '">' . self::renderIcon($this->backPath, 'gfx/button_hide.gif', $LANG->getLL('hide', 1), $ahref['toggle_visibility'], false) . '</td>';
			} else {
				$content .= '<td id="visibility_' . $id . '">' . self::renderIcon($this->backPath, 'gfx/button_unhide.gif', $LANG->getLL('unhide', 1), $ahref['toggle_visibility'], false) . '</td>';
			}

			// delete
			$content .= '<td>' .self::renderIcon($this->backPath, 'gfx/garbage.gif', $LANG->getLL('delete',1), $ahref['delete'], true) . '</td>';

// TODO colspan still constant
			$content .= '<tr id="' . $id . '" style="display: block;"><td colspan="7"></td></tr>';

		}

		if ($content) {
			$content = "<table>$content</table>";
		}
		return $content;
	}


//TODO: comment still mssing
	/* get html for this icon (may include an anchor)
	 * \param $replaceWithCleargif if set to true the icon is replaced with clear.gif, if $ahref is empty
	 */
	private static function renderIcon($path, $image, $title='', $ahref='', $replaceWithCleargif=false) {
//TODO: read width and height from file? or hardcode 16x16px?
		$width = 16;
		$height = 16;

		if ($ahref == '' && $replaceWithCleargif) {
			// hide icon
			$html = '<img' . t3lib_iconWorks::skinImg($path, 'clear.gif', 'width="' . $width . '" height="' . $height . '"') . ' title="' . $title . '" alt="" />';
		} else {
			// show icon
			$html = '<img' . t3lib_iconWorks::skinImg($path, $image, 'width="' . $width . '" height="' . $height . '"') . ' title="' . $title . '" alt="" />';
		}
		if ($ahref)
			return $ahref . $html . '</a>'; // if linked wrap in link
		return $html; // return image html code

	}


	/**
	 * gets a list of Extras associated with the current record
	 * \param String $table
	 */
//TOFO: mm query???
	private static function readList($table, $uid) {
		$list = array();
		// read all extras assigned to content in given table
#$GLOBALS['TYPO3_DB']->debugOutput = true;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_extra_content_mm',
			'content_table="' . $table . '" AND ref_content=' . $uid,
			'',
			'sorting');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($row['extra'] = self::readExtraItem($row)) { // append data for this extra (if found and accessible)
				$row['type'] = self::getExtraTitle($row['extra_type']); // get title of this extra and add to array
				$list[] = $row;
			}
		}
#t3lib_div::devlog('list', 'extra', 0, $list);
		return $list;
	}

	/**
	 *
	 */
	private static function readExtraItem($extra) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$extra['extra_type'],
			'uid=' . $extra['ref_extra']);
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			return $row;
		}
		t3lib_div::devlog('readExtraItem - referenced Extra can\'t be found in table', 'extra', 3, $extra);
		return false;
	}

//TODO: getLLL
	static function getExtraTitle($type) {
		switch($type) {
			case 'tx_extra_image':
				return 'Image';
			break;
			default:
				t3lib_div::devlog('getExtraTitle - unknown type', 'extra', 3, $type);
				return 'Unknown';
		}
	}








	/**
	 * add javascript and css files needed for display mode (adds to $GLOBALS['TYPO3backend'])
	 * \return true, if files were added
	 */
	function addAdditionalScriptToBackend() {

		switch(Extra::getDisplayMode()) {
			case EXTRA_DISPLAY_MODE_IFRAME:
				return true; // nothing to add
			break;
			case EXTRA_DISPLAY_MODE_MODAL:
			default:
			// add modalbox js to top (so modal box can be displayed over the whole backend, only onle the content frame)
			$GLOBALS['TYPO3backend']->addJavascriptFile(t3lib_extMgm::extRelPath('extra') . 'contrib/subModal/common.js');
			$GLOBALS['TYPO3backend']->addJavascriptFile(t3lib_extMgm::extRelPath('extra') . 'contrib/subModal/subModal.js');
			$GLOBALS['TYPO3backend']->addCssFile('subModal', t3lib_extMgm::extRelPath('extra') . 'contrib/subModal/subModal.css');
			return true; // javascript and css files added
			break;
		}

		return true; // nothing was added
	}


//TODO: add real functions, this is just a demo
	function isUserAllowedToPreview() {
		return true;
	}
	function isUserAllowedToEdit() {
		return true;
	}
	function isUserAllowedToViewInfo() {
		return true;
	}
	function isUserAllowedToDelete() {
		return true;
	}

}

?>
