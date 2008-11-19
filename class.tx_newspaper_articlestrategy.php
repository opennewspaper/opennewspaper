<?php
/*
 * Created on Nov 19, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class tx_newspaper_ArticleStrategy {
	public function __construct(tx_newspaper_Article $parent) {
		$this->parent = $parent;
	}
	
	public function render($template) {
		throw new NotYetImplementedException("ArticleImpl::render()");
	}
	public function importieren() {
		throw new NotYetImplementedException("ArticleImpl::importieren()");
	}
	public function exportieren() {
		throw new NotYetImplementedException("ArticleImpl::exportieren()");
	}
	public function laden() {
		throw new NotYetImplementedException("ArticleImpl::laden()");
	}
	public function speichern() {
		throw new NotYetImplementedException("ArticleImpl::speichern()");
	}
	public function vergleichen() {
		throw new NotYetImplementedException("ArticleImpl::vergleichen()");
	}
	public function extraAnlegen() {
		throw new NotYetImplementedException("ArticleImpl::extraAnlegen()");
	}

	/// returns an actual member (-> Extra)
	function getAttribute($attribute) {
		return $this->attributes[$attribute];
	}

	/// sets a member (-> Extra)
	function setAttribute($attribute, $value) {
		$this->attributes[$attribute] = $value;
	}

	/// Get the list of Extra s associated with this Article
	function getExtras() { return $this->extras; }

	function addExtra() {
		throw new NotYetImplementedException("ArticleImpl::addExtra()");
	}

	function getSource() { return $this->source; }

	function setSource(tx_newspaper_Source $source) {$this->source = $source; }

	function getUid() { return $this->uid; }
	function setUid($uid) { $this->uid = $uid; }
	
	static function getAttributeList() { return self::$attribute_list; }

	static function mapFieldToSourceField($fieldname, 
										  tx_newspaper_Source $source, 
										  array $mapFieldsToSourceFields) {
		$mapping = $mapFieldsToSourceFields[get_class($source)];

		if (!$mapping) 
			throw new tx_newspaper_WrongClassException('No mapping configured for Source type '.
										  get_class($source));

		return $mapping[$fieldname];
	}
	
	static function sourceTable(tx_newspaper_Source $source, array $sourceTable) {
		$table = $sourceTable[get_class($source)];

		if (!$table) 
			throw new tx_newspaper_WrongClassException('No mapping configured for Source type '.
										  get_class($source));

		return $table;		
	}
	
	private $parent = null;
}

?>
