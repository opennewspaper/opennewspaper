<?php
/*
 * Created on Nov 19, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

/// Behavior class to factor out code common to more or less all Article implementations
class tx_newspaper_ArticleBehavior {
	
	/** \param $parent The tx_newspaper_Article object using this Strategy
	 */ 
	public function __construct(tx_newspaper_Article $parent) {
		$this->parent = $parent;
	}
	
	public function render($template) {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::render()");
	}
	public function importieren() {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::importieren()");
	}
	public function exportieren() {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::exportieren()");
	}
	public function laden() {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::laden()");
	}
	public function speichern() {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::speichern()");
	}
	public function vergleichen() {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::vergleichen()");
	}
	public function extraAnlegen() {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::extraAnlegen()");
	}	
	static function getAttributeList() {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::addExtra()");
	}

	/** Usage:
	 *  \code return tx_newspaper_ArticleStrategy::mapFieldToSourceField(
	 *		$fieldname, $source, self::$mapFieldsToSourceFields); \endcode
	 */
	static function mapFieldToSourceField($fieldname, 
										  tx_newspaper_Source $source, 
										  array $mapFieldsToSourceFields) {
		$mapping = $mapFieldsToSourceFields[get_class($source)];

		if (!$mapping) 
			throw new tx_newspaper_WrongClassException('No mapping configured for Source type '.
										  get_class($source));

		return $mapping[$fieldname];
	}
	
	/** Usage:
	 * \code return tx_newspaper_ArticleStrategy::sourceTable(
	 * 		$source, self::$table); \endcode
	 */
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
