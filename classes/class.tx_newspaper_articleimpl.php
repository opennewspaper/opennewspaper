<?php
/**
 *  \file class.tx_newspaper_articleimpl.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_article.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_extra.php');

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_articlebehavior.php');

/// An article for the online newspaper
/** \todo The names for the functions are not defined yet. The interface
 *  (Article) is not yet ready either.
 *  \todo take over all functionality from tx_newspaper_PageZone_Article. that includes:
 *  - generate a record in tx_newspaper_pagezone in addition to tx_newspaper_exra
 *  - remove pagezone_article and extra_articlerenderer and replace with articleimpl everywhere
 *  - rename to article and interface to articleiface
 *  - make sure it works as generic page zone (when assembling pages) as well as concrete article 
 */
class tx_newspaper_Article extends tx_newspaper_PageZone 
	implements tx_newspaper_ArticleIface {

	public function __construct($uid = 0) {
		$this->articleBehavior = new tx_newspaper_ArticleBehavior($this);
		if ($uid) {
			$this->setUid($uid);
			
			/** \todo I'm not sure whether the following line should remain. It's a
			 *  safety net because currently it's not ensured that extras are 
			 *  created consistently.
			 */
			$this->extra_uid = tx_newspaper_ExtraImpl::createExtraRecord($uid, $this->getTable());	
			$this->pagezone_uid = tx_newspaper_PageZone::createPageZoneRecord($uid, $this->getTable());
		}	
	}
	
	public function render($template = '') {
		return '<h2>'.$this->getAttribute('kicker').'</h2>'.'<h1>'.$this->getAttribute('title').'</h1>'.
			   '<h3>'.$this->getAttribute('teaser').'</h3>'.'<p>VON '.$this->getAttribute('author').'</p>'.
			   $this->getAttribute('text');
		/// \todo use smarty
		/// \todo print extras
		/// \todo handle case where $this is a placeholder for an actual article (formerly Extra_ArticleRenderer)
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
	public function vergleichen() {
		$this->articleBehavior->vergleichen();
	}
	public function extraAnlegen() {
		$this->articleBehavior->extraAnlegen();
	}

	/// returns an actual member (-> Extra)
	function getAttribute($attribute) {

		if (!$this->attributes) {
			$this->attributes = $this->readExtraItem($this->getUid(), $this->getTable());
		}

 		if (!array_key_exists($attribute, $this->attributes)) {
        	throw new tx_newspaper_WrongAttributeException($attribute, $this->getUid());
 		}

 		return $this->attributes[$attribute];
	}

	/// sets a member (-> Extra)
	function setAttribute($attribute, $value) {
		if (!$this->attributes) {
			$this->attributes = $this->readExtraItem($this->getUid(), $this->getTable());
		}
		
		$this->attributes[$attribute] = $value;
	}

	/// Get the list of Extra s associated with this Article
	function getExtras() { 
		if (!$this->extras) {
			$extras = tx_newspaper::selectRows(
				'uid_foreign', 'tx_newspaper_article_extras_mm', 
				'uid_local = ' . $this->getUid());
			if ($extras) foreach ($extras as $extra) {
				try {
					$new_extra = tx_newspaper_Extra_Factory::create($extra['uid_foreign']);
					$this->extras[] = $new_extra;
				} catch(tx_newspaper_EmptyResultException $e) {
					/// remove mm-table entry if the extra pointed to doesn't exist
					$query = $GLOBALS['TYPO3_DB']->DELETEquery(
						'tx_newspaper_article_extras_mm', 'uid_foreign = ' . intval($extra['uid_foreign']));
					t3lib_div::debug($query);
					$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
		return $this->extras; 
	}

	function addExtra(tx_newspaper_Extra $newExtra) {
		throw new tx_newspaper_NotYetImplementedException();
	}

	function getSource() { return $this->source; }

	function setSource(tx_newspaper_Source $source) {
		$this->source = $source;
		$this->setAttribute('source_object', serialize($source)); 
	}

	function getUid() { return intval($this->uid); }
	function setUid($uid) { 
		$this->uid = $uid;
		 $this->setAttribute('source_id', $uid);
	}
	
	public function store() {
		
		/// insert article data (if uid == 0) or update if uid > 0
		if ($this->getUid()) {
			/// If the attributes are not yet in memory, read them now
			if (!$this->attributes) { 
				$this->attributes = $this->readExtraItem($this->getUid(), $this->getTable());
			}
			
			tx_newspaper::updateRows(
				$this->getTable(), 'uid = ' . $this->getUid(), $this->attributes
			);
		} else {
			$this->setUid(
				tx_newspaper::insertRows(
					$this->getTable(), $this->attributes
				)
			);
		}
		
		/// store all extras and make sure they are in the MM relation table
		if ($this->extras) foreach ($this->extras as $extra) {
			$extra_uid = $extra->store();
			$extra_table = $extra->getTable();
			self::relateExtra2Article($extra_table, $extra_uid, getUid());
		}
		
		return $this->getUid();		
	}
	
	public static function relateExtra2Article($extra_table, $extra_uid, $article_uid) {
		
		$extra_table = strtolower($extra_table);
		
		$abstract_uid = tx_newspaper_ExtraImpl::createExtraRecord($extra_uid, $extra_table); 
		
		/// \todo write entry in MM table (if not exists)
		$row = tx_newspaper::selectZeroOrOneRows(
			'uid_local', 
			tx_newspaper_Extra_Factory::getExtra2ArticleTable(),
			'uid_local = ' . intval($article_uid) .
			' AND uid_foreign = ' . intval($abstract_uid)	
		);
		if ($row['uid_local'] != $article_uid || 
			$row['uid_foreign'] != $abstract_uid) {
			if (!tx_newspaper::insertRows(
					tx_newspaper_Extra_Factory::getExtra2ArticleTable(),
					array(
						'uid_local' => $article_uid,
						'uid_foreign' => $abstract_uid)
					)
				) {
				return false;					
			}
		} 
		
		return $abstract_uid;
		
	}
	
	static function getAttributeList() { return self::$attribute_list; }

	static function mapFieldToSourceField($fieldname, tx_newspaper_Source $source) {
		return tx_newspaper_ArticleBehavior::mapFieldToSourceField($fieldname, $source,
																   self::$mapFieldsToSourceFields);
	}
	
	static function sourceTable(tx_newspaper_Source $source) {
		return tx_newspaper_ArticleBehavior::sourceTable($source, self::$table);
	}

	public function getTable() {
		return 'tx_newspaper_article';
	}

	/** \todo Internationalization */
	static function getTitle() {
		return 'ArticleImpl';
	}
	
	static function getModuleName() {
		return 'np_article';
	}

	static function readExtraItem($uid, $table) {
		if (!$uid) return array();
		return tx_newspaper::selectOneRow('*', $table, 'uid=' . $uid);
	}
	
	protected function getExtra2PagezoneTable() {
		return self::$extra_2_pagezone_table;
	}

	static protected $extra_2_pagezone_table = 'tx_newspaper_pagezone_article_extras_mm';
	
	////////////////////////////////////////////////////////////////////////////
	
	private $source = null;			///< Source the ArticleImpl is read from
	private $uid = '';				///< UID that identifies the article in the source
	
	private $extra_uid = null;		///< article's UID in the abstract Extra table
	private $pagezone_uid = null;	///< article's UID in the abstract PageZone table

	private $articleBehavior = null;	///< Object to delegate operations to
	
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
	
}
?>
