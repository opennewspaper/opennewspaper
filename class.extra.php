<?php

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_extra_be.php');

class Extra {

	private static $registeredExtra = array(); // list of registered Extras


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
		$content = tx_newspaper_ExtraBE::getJsForExtraForm();


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
//TODO: mm query??? could fields conf and position be read?
	private static function readList($table, $uid) {
		$list = array();
		// read all extras assigned to content in given table
#$GLOBALS['TYPO3_DB']->debugOutput = true;
//TODO: name of table??? tx_newspaper_content_mm ???
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
#t3lib_div::devlog('list', 'newspaper', 0, $list);
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
		t3lib_div::devlog('readExtraItem - referenced Extra can\'t be found in table', 'newspaper', 3, $extra);
		return false;
	}

//TODO: getLLL
	static function getExtraTitle($type) {
		switch($type) {
			case 'tx_extra_image':
				return 'Image';
			break;
			default:
				t3lib_div::devlog('getExtraTitle - unknown type', 'newspaper', 3, $type);
				return 'Unknown';
		}
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
