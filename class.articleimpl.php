<?php
/**
 *  \file class.articleimpl.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/interface.article.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/interface.extra.php');

/// An article for the online newspaper
/** \todo The names for the functions are not defined yet. The interface
 *  (Article) is not yet ready either. In fact, this is just a dummy class.
 */
class ArticleImpl implements Article {

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

	function setSource(Source $source) {$this->source = $source; }

	function getUid() { return $this->uid; }
	function setUid($uid) { $this->uid = $uid; }
	
	static function getAttributeList() { return self::$attribute_list; }

	static function mapFieldToSourceField($fieldname, Source $source) {
		$mapping = self::$mapFieldsToSourceFields[get_class($source)];

		if (!$mapping) 
			throw new WrongClassException('No mapping configured for Source type '.
										  get_class($source));

		return $mapping[$fieldname];
	}
	
	private $extras = null;			///< array of Extra s
	private $source = null;			///< Source the ArticleImpl is read from
	private $attributes = array();	///< array of attributes
	private $uid = '';				///< UID that identifies the article in the source
	
	///	List of attributes that together constitute an ArticleImpl
	private static $attribute_list = array(
		'title', 'teaser', 'text', 'ressort'
	);
	
	private static $mapFieldsToSourceFields = array(
		'taz_RedsysSource' => array(
	    	'title' => 'Titel',
	    	'teaser' => 'Titel2',
	    	'text' => 'Text',
	    	'ressort' => 'OnRes' 
		)
	);
}
?>
