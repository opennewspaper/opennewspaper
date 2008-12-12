<?php
/**
 *  \file class.tx_newspaper_extraimpl.php
 *
 *  \author Oliver Schröder <newspaper@schroederbros.de>
 *  \date Dec 12, 2008
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_extra.php');

/// An Extra for the online newspaper
/** \todo This is just a dummy class.
 */
class tx_newspaper_ExtraImpl implements tx_newspaper_Extra {


	public function render($template) {
		throw new NotYetImplementedException("ExtraImpl::render()");
	}

	public function getAttribute($fieldname) {
		throw new NotYetImplementedException("EytraImpl::getAttribute()");
	}

	public function setAttribute($fieldname, $value) {
		throw new NotYetImplementedException("ExtraImpl::setAttribute()");
	}

	function getSource() {
		throw new NotYetImplementedException("ExtraImpl::getSource()");
	}

	function setSource(tx_newspaper_Source $source) {
		throw new NotYetImplementedException("ExtraImpl::setSource()");
	}

	static function mapFieldToSourceField($fieldname, tx_newspaper_Source $source) {
		throw new NotYetImplementedException("ExtraImpl::mapFieldToSourceField()");
	}

	static function sourceTable(tx_newspaper_Source $source) {
		throw new NotYetImplementedException("ExtraImpl::sourceTable()");
	}

	static function getName() {
		throw new NotYetImplementedException("ExtraImpl::getName()");
	}

	static function getTitle() {
		throw new NotYetImplementedException("ExtraImpl::getTitle()");
	}



}
?>
