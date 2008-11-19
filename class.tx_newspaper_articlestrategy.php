<?php
/*
 * Created on Nov 19, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

/// Strategy class to factor out code common to more or less all Article implementations
/** I made this class implement the Article interface. I'm not sure if this is a
 *  bright idea, but I think that as every function in an Article implementation
 *  just calls the corresponding function in the Strategy class, this class must
 *  implement every function in the interface, right? So I just as well might 
 *  make the Strategy class conform to the interface. As an added bonus, I get
 *  doxygen documentation for every function for free.
 * 
 *  The catch here is that
 *  -# The ArticleStrategy IS NOT A Article
 *  -# Maybe there are methods which are not delegated to the Strategy.
 * 
 *  I'll just see how this approach works out. Maybe I'll change it later.
 */ 
class tx_newspaper_ArticleStrategy implements tx_newspaper_Article {
	
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

	/// returns an actual member (-> Extra)
	function getAttribute($attribute) {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::addExtra()");
	}

	/// sets a member (-> Extra)
	function setAttribute($attribute, $value) {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::addExtra()");
	}

	/// Get the list of Extra s associated with this Article
	function getExtras() {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::addExtra()");
	}

	function addExtra() {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::addExtra()");
	}

	function getSource() {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::addExtra()");
	}

	function setSource(tx_newspaper_Source $source) {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::addExtra()");
	}

	function getUid() {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::addExtra()");
	}
	function setUid($uid) {
		throw new NotYetImplementedException("tx_newspaper_ArticleStrategy::addExtra()");
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
