<?php
/**
 *  \file class.tx_newspaper_articlebehavior.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Nov 19, 2008
 */

/// Behavior class to factor out code common to more or less all Article implementations
class tx_newspaper_ArticleBehavior {
	
	/** \param $parent The tx_newspaper_Article object using this Behavior
	 */ 
	public function __construct(tx_newspaper_Article $parent) {
		$this->parent = $parent;
	}
	
	public function render($template= '') {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_ArticleBehavior::render()");
	}
	public function importieren() {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_ArticleBehavior::importieren()");
	}
	public function exportieren() {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_ArticleBehavior::exportieren()");
	}
	public function laden() {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_ArticleBehavior::laden()");
	}
	public function speichern() {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_ArticleBehavior::speichern()");
	}
	public function vergleichen() {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_ArticleBehavior::vergleichen()");
	}
	public function extraAnlegen() {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_ArticleBehavior::extraAnlegen()");
	}	
	static function getAttributeList() {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_ArticleBehavior::addExtra()");
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
