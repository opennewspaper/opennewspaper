<?php

require_once(BASEPATH . '/typo3conf/ext/newspaper/classes/class.tx_newspaper_extraimpl.php');

class tx_newspaper_extra_ArticleRenderer extends tx_newspaper_ExtraImpl {

	/** Just a quick hack to see anything
	 *  \todo use smarty
	 */
	public function render($template = '') {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_extra_ArticleRenderer::render()");
	}


// internal name
	static function getName() {
		return 'tx_newspaper_extra_articlerenderer';
	}

//TODO: getLLL
	static function getTitle() {
		return 'ArticleRenderer';
	}

// title for module
	static function getModuleName() {
		return 'npe_rend'; // this is the default folder for data associated with newspaper etxension, overwrite in conrete Extras
	}
}

?>