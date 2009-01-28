<?php
/**
 *  \file class.tx_newspaper_articleimpl.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_article.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_extra.php');

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_articlebehavior.php');

/// An article for the online newspaper
/** \todo The names for the functions are not defined yet. The interface
 *  (Article) is not yet ready either. In fact, this is just a dummy class.
 */
class tx_newspaper_ArticleImpl implements tx_newspaper_Article {

	public function __construct($uid = 0) {
		$this->articleBehavior = new tx_newspaper_ArticleBehavior($this);
		if ($uid) {
			$this->setUid($uid);
			$this->attributes = $this->readExtraItem($uid, $this->getName());
		}	
	}
	
	public function render($template = '') {
		return '<h2>'.$this->getAttribute('kicker').'</h2>'.'<h1>'.$this->getAttribute('title').'</h1>'.
			   '<h3>'.$this->getAttribute('teaser').'</h3>'.'<p>VON '.$this->getAttribute('author').'</p>'.
			   $this->getAttribute('text');
		throw new tx_newspaper_NotYetImplementedException("ArticleImpl::render()");
	}
	
	public function importieren(tx_newspaper_Source $quelle) {
		$this->articleBehavior->importieren($quelle);
	}
	public function exportieren(tx_newspaper_Source $quelle) {
		$this->articleBehavior->exportieren($quelle);
	}
	public function laden() {
		$this->articleBehavior->laden();
	}
	public function speichern() {
		$this->articleBehavior->speichern();
	}
	public function vergleichen() {
		$this->articleBehavior->vergleichen();
	}
	public function extraAnlegen() {
		$this->articleBehavior->extraAnlegen();
	}

	/// returns an actual member (-> Extra)
	function getAttribute($attribute) {
		/// \todo throw exception if wrong attribute; see ExtraImpl
		return $this->attributes[$attribute];
	}

	/// sets a member (-> Extra)
	function setAttribute($attribute, $value) {
		$this->attributes[$attribute] = $value;
	}

	/// Get the list of Extra s associated with this Article
	function getExtras() { return $this->extras; }

	function addExtra(tx_newspaper_Extra $newExtra) {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_ArticleImpl::addExtra()");
	}

	function getSource() { return $this->source; }

	function setSource(tx_newspaper_Source $source) {$this->source = $source; }

	function getUid() { return $this->uid; }
	function setUid($uid) { $this->uid = $uid; }
	
	static function getAttributeList() { return self::$attribute_list; }

	static function mapFieldToSourceField($fieldname, tx_newspaper_Source $source) {
		return tx_newspaper_ArticleBehavior::mapFieldToSourceField($fieldname, $source,
																   self::$mapFieldsToSourceFields);
	}
	
	static function sourceTable(tx_newspaper_Source $source) {
		return tx_newspaper_ArticleBehavior::sourceTable($source, self::$table);
	}

	static function getName() {
		return 'tx_newspaper_article';
	}

	/** \todo Internationalization */
	static function getTitle() {
		return 'ArticleImpl';
	}
	
	static function getModuleName() {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_ArticleImpl::getModuleName()");
	}

	static function readExtraItem($uid, $table) {
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			'*',
			$table,
			'uid=' . $uid);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		
		if (!$res) {
        	throw new tx_newspaper_NoResException($query);
        }
        
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        
		if (!$row) {
			throw new tx_newspaper_EmptyResultException($query);
		}

		return $row;
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	private $extras = null;			///< array of Extra s
	private $source = null;			///< Source the ArticleImpl is read from
	private $attributes = array();	///< array of attributes
	private $uid = '';				///< UID that identifies the article in the source
	
	///	List of attributes that together constitute an ArticleImpl
	private static $attribute_list = array(
		'title', 'teaser', 'text', 'ressort'
	);
	
	/// Mapping of the attributes to the names they have in the Source for each supported Source type
	/** Form of the array:
	 *  \code
	 *  source_class_name => array (
	 * 		attribute_name => name_of_that_attribute_in_source
	 * 		...		
	 *  )
	 * \endcode 
	 * the attributes from \p $attribute_list must be the same as here.
	 */
	private static $mapFieldsToSourceFields = array(
		'tx_newspaper_taz_RedsysSource' => array(
	    	'title' => 'Titel',
	    	'teaser' => 'Titel2',
	    	'text' => 'Text',
	    	'ressort' => 'OnRes' 
		),
		'tx_newspaper_DBSource' => array(
	    	'title' => 'article_manualtitle',
	    	'teaser' => 'article_title2',
	    	'text' => 'article_manualtext',
	    	'ressort' => 'ressort' 
		)
	);
	
	///	Additional info needed to instantiate an article for each supported Source type
	private static $table = array(
		'tx_newspaper_taz_RedsysSource' => '',
		'tx_newspaper_DBSource' => 'tx_hptazarticle_list'
	);
	
	private $articleBehavior = null;
}
?>
