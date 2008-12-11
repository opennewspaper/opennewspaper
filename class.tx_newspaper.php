<?php


class tx_newspaper {

	/**
	 * add javascript (or other script parts) to extra form (basically containing an onunload script)
	 * \param $PA typo3 standard for userFunc
	 * \param $fobj typo3 standard for userFunc
	 * \return String html code to be placed in the html header <script ...></script>
	 */
	public function getCodeForBackend($PA, $fobj) {
		$js = '';
		switch(Extra::getDisplayMode()) {
			case EXTRA_DISPLAY_MODE_IFRAME:
				$js = '<script type="text/javascript" src="' . t3lib_extMgm::extRelPath('extra') . 'res/extra_form_iframe.js"></script>';
			break;
			case EXTRA_DISPLAY_MODE_MODAL:
			default:
				$js = '<script type="text/javascript" src="' . t3lib_extMgm::extRelPath('extra') . 'res/extra_form_modalbox.js"></script>';
			break;
		}
		return $js;
	}


	/**
	 * add Extra list to backend form
	 * \param $PA typo3 standard for userFunc
	 * \param $fobj typo3 standard for userFunc
	 * \return String html code to be placed in the html header <script ...></script>
	 */
	function renderList($PA, $fobj) {
#t3lib_div::devlog('pa', 'extra', 0, $PA);

//TODO: can/should articles be hard-coded here?
		// get table and uid of current record
		$current_record['table'] = $PA['table'];
		$current_record['uid'] = $PA['row']['uid'];

		//$extra = new Extra();
		return Extra::renderList($current_record['table'], $current_record['uid']);

	}


}

?>
