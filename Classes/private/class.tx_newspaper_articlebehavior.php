<?php
/**
 *  \file class.tx_newspaper_articlebehavior.php
 *
 *  \author Lene Preuss <lene.preuss@gmx.net>
 *  \date Nov 19, 2008
 */

/** Behavior class to factor out code common to more or less all 
 *  tx_newspaper_ArticleIface implementations.
 * 
 *  \todo This class seems rather obsolete because I decided that there is only
 * 		one Article implementation, tx_newspaper_Article. Merge this class with
 * 		tx_newspaper_Article.
 */
class tx_newspaper_ArticleBehavior {
	
	/** \param $parent The tx_newspaper_Article object using this Behavior
	 */ 
	public function __construct(tx_newspaper_Article $parent) {
		$this->parent = $parent;
	}
	
	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		return get_class($this) . '-object ' . "\n";
	}
	
	public function render($template= '') {
		throw new tx_newspaper_NotYetImplementedException();
	}
	public function importieren() {
		throw new tx_newspaper_NotYetImplementedException();
	}
	public function exportieren() {
		throw new tx_newspaper_NotYetImplementedException();
	}
	public function laden() {
		throw new tx_newspaper_NotYetImplementedException();
	}
	public function vergleichen() {
		throw new tx_newspaper_NotYetImplementedException();
	}
	public function extraAnlegen() {
		throw new tx_newspaper_NotYetImplementedException();
	}	
	static function getAttributeList() {
		throw new tx_newspaper_NotYetImplementedException();
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
