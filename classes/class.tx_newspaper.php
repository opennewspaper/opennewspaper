<?php

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_extra_be.php');

#t3lib_div::devlog('class.tx_newspaper.php loaded', 'newspaper', 0);


// is this class still needed or can these two methods be moved to tx_newspaper_extra_be.php?
class tx_newspaper {

	/**
	 * add javascript (or other script parts) to extra form (basically containing an onunload script)
	 * \param $PA typo3 standard for userFunc
	 * \param $fobj typo3 standard for userFunc
	 * \return String html code to be placed in the html header <script ...></script>
	 */
	public function getCodeForBackend($PA, $fobj) {
#t3lib_div::devlog('tx_newspaper->getCodeForBackend', 'newspaper', 0);
		return tx_newspaper_ExtraBE::getJsForExtraField();
	}


	/**
	 * add Extra list to backend form
	 * \param $PA typo3 standard for userFunc
	 * \param $fobj typo3 standard for userFunc
	 * \return String html code to be placed in the html header <script ...></script>
	 */
	function renderList($PA, $fobj) {
#t3lib_div::devlog('tx_newspaper->renderList pa', 'newspaper', 0, $PA);

//TODO: can/should articles be hard-coded here?
		// get table and uid of current record
		$current_record['table'] = $PA['table'];
		$current_record['uid'] = $PA['row']['uid'];

		return tx_newspaper_ExtraBE::renderList($current_record['table'], $current_record['uid']);

	}


}

?>
