<?php
/**
 *  \file class.tx_newspaper_articleimpl.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_articleiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_extraiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_writeslog.php');

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
	implements tx_newspaper_ArticleIface, tx_newspaper_WritesLog {

	////////////////////////////////////////////////////////////////////////////
	//
	//	magic methods ( http://php.net/manual/language.oop5.magic.php )
	//
	////////////////////////////////////////////////////////////////////////////
	
	public function __construct($uid = 0) {
		$this->articleBehavior = new tx_newspaper_ArticleBehavior($this);
		$this->smarty = new tx_newspaper_Smarty();
		
		if ($uid) {
			$this->setUid($uid);
			
			/** \todo I'm not sure whether the following line should remain. It's a
			 *  safety net because currently it's not ensured that extras are 
			 *  created consistently.
			 */
			$this->extra_uid = tx_newspaper_Extra::createExtraRecord($uid, $this->getTable());	
/// \todo: can this be done this way???
#if (TYPO3_MODE == 'FE') {
			$this->pagezone_uid = $this->createPageZoneRecord();
#}
		}
		
	}

	///	Things to do after an article is \p clone d
	/** This magic function is called after all attributes of a tx_newspaper_Article
	 *  have been copied, when the PHP operation \p clone is executed on a 
	 *  tx_newspaper_Article. 
	 * 
	 * 	It ensures that all attributes are read from DB, crdate and tstamp are
	 *  updated, and the new Article is written to DB. Also, the Extras of the 
	 *  Article are \p clone d. 
	 */
	public function __clone() {
 		/*  ensure attributes are loaded from DB. readExtraItem() isn't  
 		 *  called here because maybe the content is already there and it would
 		 *  cause the DB operation to be done twice.
 		 */
		$this->getAttribute('uid');
		
		//  unset the UID so the object can be written to a new DB record.
 		$this->attributes['uid'] = 0;
 		$this->setUid(0);

 		$this->setAttribute('crdate', time());
 		$this->setAttribute('tstamp', time());
 		
 		$this->store();
 		
 		/// \todo clone extras
 		$old_extras = $this->getExtras();
 		$this->extras = array();
 		foreach ($old_extras as $old_extra) {
 			$this->extras[] = clone $old_extra;
 		}
 	}
	
	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		return get_class($this) . '-object ' . "\n" .
			   'attributes: ' . print_r($this->attributes, 1) . "\n" .
			   'extras: ' . print_r($this->extras, 1) . "\n";
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_StoredObject
	//
	////////////////////////////////////////////////////////////////////////////

	/// returns an actual member (-> Extra)
	public function getAttribute($attribute) {
				
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
				'*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
			);
		}
		
 		if (!array_key_exists($attribute, $this->attributes) && $this->getUid()) {
        	throw new tx_newspaper_WrongAttributeException($attribute, $this->getUid());
 		}

 		return $this->attributes[$attribute];
	}

	/// sets a member (-> Extra)
	public function setAttribute($attribute, $value) {
		if (!$this->attributes) {
			$this->attributes = $this->readExtraItem($this->getUid(), $this->getTable());
		}
		
		$this->attributes[$attribute] = $value;
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
			$this->setAttribute('pid', tx_newspaper_Sysfolder::getInstance()->getPid($this));
			$this->setUid(
				tx_newspaper::insertRows(
					$this->getTable(), $this->attributes
				)
			);
		}

		/// Ensure the page zone has an entry in the abstract supertable...
		$pagezone_uid = $this->createPageZoneRecord($this->getUid(), $this->getTable());
		/// ... and is attached to the correct page
		if ($this->getParentPage() && $this->getParentPage()->getUid()) {
			tx_newspaper::updateRows(
				'tx_newspaper_pagezone', 
				'uid = ' . $pagezone_uid, 
				array('page_id' => $this->getParentPage()->getUid())
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

	/** \todo Internationalization */
	public function getTitle() {
		return 'Article';
	}

	public function getUid() { 
		if (!intval($this->uid)) $this->uid = $this->attributes['uid'];
		return intval($this->uid);
	}

	public function setUid($uid) { 
		$this->uid = $uid;
		if ($this->attributes) $this->attributes['source_id'] = $uid;
	}

	public public function getTable() {
		return 'tx_newspaper_article';
	}

	static public function getModuleName() {
		return 'np_article';
	}
	
	
	/// adds sections to article (will be inserted after existing sections)
	/// \param section object
	public function addSection(tx_newspaper_Section $s) {
/// \todo: if ($this->getuid() == 0) throw e OR 		
/// \todo: just collect here and store sections later in article::store()
		
		// get pos of next element 
		$p = tx_newspaper::getLastPosInMmTable('tx_newspaper_article_sections_mm', $this->getuid()) + 1;
		
		tx_newspaper::insertRows(
			'tx_newspaper_article_sections_mm',
			array(
				'uid_local' => $this->getUid(),
				'uid_foreign' => $s->getUid(),
				'sorting' => $p
			)
		);
		
	}
	

	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_ExtraIface
	//
	////////////////////////////////////////////////////////////////////////////
		
	/// Renders an article
	public function render($template_set = '') {
		
		/** Check whether to use a specific template set.
		 *	This must be done regardless if this is a template used to define
		 *	default placements for articles, or an actual article
		 */
		if ($this->getAttribute('template_set')) {
			$template_set = $this->getAttribute('template_set');
		}

		if ($this->getAttribute('is_template')) {
		
			/** Handle case where $this is a placeholder for an actual article
			 *  (formerly Extra_ArticleRenderer)
			 */
			$ret = '';
			$article = new tx_newspaper_article(t3lib_div::_GP(tx_newspaper::GET_article()));
			$ret = $article->render($template_set);
			
		} else {
		
			/// Configure Smarty rendering engine
			if ($template_set) {
				$this->smarty->setTemplateSet($template_set);
			}
			if ($this->getCurrentPage()->getUID() && $this->getCurrentPage()->getPageType()) {
				$this->smarty->setPageType($this->getCurrentPage());
			}
			if ($this->getPageZoneType()) {
				$this->smarty->setPageZoneType($this);
			}

			$this->smarty->assign('kicker', $this->getAttribute('kicker'));
			$this->smarty->assign('title', $this->getAttribute('title'));
			$this->smarty->assign('teaser', $this->getAttribute('teaser'));
			$this->smarty->assign('author', $this->getAttribute('author'));
			$this->smarty->assign('text', $this->getAttribute('text'));

			/** Assemble the text paragraphs and extras in an array of the form:
			 *  \code
			 *  array(
			 *  	$paragraph_number => array(
			 * 			"text" => $text_of_paragraph,
			 *          "extras" => array(
			 * 				$position => $rendered_extra,
			 * 				...
			 * 			)
			 * 		),
			 * 		...
			 *  )
			 *  \endcode
			 */
			$text_paragraphs = $this->splitIntoParagraphs();
			$paragraphs = array();
			foreach ($text_paragraphs as $index => $text_paragraph) {
				$paragraph = array();
				if ($text_paragraph) $paragraph['text'] = $text_paragraph;
				foreach ($this->getExtras() as $extra) {
					if ($extra->getAttribute('paragraph') == $index ||
						sizeof($text_paragraphs)+$extra->getAttribute('paragraph') == $index) {
						$paragraph['extras'][$extra->getAttribute('position')] .= $extra->render($template_set);
					}
				}
				/*  Braindead PHP does not sort arrays automatically, even if
				 *  the keys are integers. So if you, e.g., insert first $a[4]
				 *  and then $a[2], $a == array ( 4 => ..., 2 => ...).
				 *  Thus, you must call ksort.
				 */
				if ($paragraph['extras']) ksort($paragraph['extras']);
				$paragraphs[] = $paragraph;
			}
			
			/** Make sure all extras are rendered, even those whose 'paragraph'
			 *  attribute is greater than the number of text paragraphs or less
			 *  than its negative.
			 */ 		
			foreach ($this->getExtras() as $extra) {
				if ($extra->getAttribute('paragraph')+sizeof($text_paragraphs) < 0) {
					$paragraphs[0]['extras'][] = $extra->render($template_set);
				} else if ($extra->getAttribute('paragraph') > sizeof($text_paragraphs)) {
					$paragraphs[sizeof($paragraphs)-1]['extras'][] = $extra->render($template_set);
				}
			}

			$this->smarty->assign('paragraphs', $paragraphs);
			$ret = $this->smarty->fetch($this);
		} 
		return $ret;
	}

	static public function readExtraItem($uid, $table) {
		if (!$uid) return array();
		return tx_newspaper::selectOneRow('*', $table, 'uid=' . $uid);
	}

	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_WithSource
	//
	////////////////////////////////////////////////////////////////////////////

	public function getSource() { return $this->source; }

	public function setSource(tx_newspaper_Source $source) {
		$this->source = $source;
		$this->setAttribute('source_object', serialize($source)); 
	}

	static public function mapFieldToSourceField($fieldname, tx_newspaper_Source $source) {
		return tx_newspaper_ArticleBehavior::mapFieldToSourceField($fieldname, $source,
																   self::$mapFieldsToSourceFields);
	}
	
	static public function sourceTable(tx_newspaper_Source $source) {
		return tx_newspaper_ArticleBehavior::sourceTable($source, self::$table);
	}

	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_ArticleIface
	//
	////////////////////////////////////////////////////////////////////////////
	
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

	/// Get the list of Extra s associated with this Article
	public function getExtras() { 
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
		debug($this->getUid(), 'Article::UID ', __LINE__, __FILE__);
		debug($this->extras, 'Article::getExtras() ', __LINE__, __FILE__);
#		die();
		
		usort($this->extras, array(get_class($this), 'compareExtras')); 
		
		return $this->extras; 
	}
/*
	public function addExtra(tx_newspaper_Extra $newExtra) {
		throw new tx_newspaper_NotYetImplementedException();
	}
*/
	////////////////////////////////////////////////////////////////////////////
	//
	//	class tx_newspaper_PageZone
	//
	////////////////////////////////////////////////////////////////////////////

	public function getPageZoneType() {
		if (!$this->pagezonetype) {
			$pzt = tx_newspaper::selectOneRow('uid', 'tx_newspaper_pagezonetype', 'is_article');
			$pagezonetype_id = $pzt['uid'];
			$this->pagezonetype = new tx_newspaper_PageZoneType($pagezonetype_id);
		}
		return $this->pagezonetype; 
	}

	////////////////////////////////////////////////////////////////////////////
	//
	//	class tx_newspaper_Article
	//
	////////////////////////////////////////////////////////////////////////////
	
	public function clearExtras() {
		$this->extras = array();	
	}
	
	static public function relateExtra2Article($extra_table, $extra_uid, $article_uid) {
		
		$extra_table = strtolower($extra_table);
		
		$abstract_uid = tx_newspaper_Extra::createExtraRecord($extra_uid, $extra_table); 
		
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
	
	static public function getAttributeList() { return self::$attribute_list; }
	
	
	/// gets a list of tx_newspaper_Article objects assigned to given article type
	/** \param tx_newspaper_ArticleType article type object
	 *  \param int limit max number of records to read (default: 10), if negative no limit is used
	 *  \return array with tx_newspaper_Article objects 
	 */
	static public function listArticlesWithArticletype(tx_newspaper_ArticleType $at, $limit=10) {

		$limit = intval($limit);
		$limit_part = ($limit > 0)? '0,' . $limit : ''; 
		
		$row = tx_newspaper::selectRows(
			'uid',
			'tx_newspaper_article',
			'deleted=0 AND articletype_id=' . $at->getUid(),
			'',
			'tstamp DESC',
			$limit_part
		);

		$list = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$list[] = new tx_newspaper_Article($row[$i]['uid']);
		}
		return $list;
	}
	

	////////////////////////////////////////////////////////////////////////////
	//
	//	protected functions
	//
	////////////////////////////////////////////////////////////////////////////
	
	protected function getCurrentPage() {
		$section = tx_newspaper::getSection();
		return new tx_newspaper_Page($section, new tx_newspaper_PageType($_GET));
	}
	
	protected function splitIntoParagraphs() {
		/** A text usually starts with a <p>, in that case the first paragraph
		 *  must be removed. It may not be the case though, if so, the first
		 *  paragraph is meaningful and must be kept.
		 */
		$paragraphs = explode('<p', $this->getAttribute('text'));		
		
		foreach ($paragraphs as $index => $paragraph) {
			/// remove the test of the <p>-tag from every line
			$paragraphs[$index] = trim(substr($paragraph, strpos($paragraph, '>')+1));
			/** each paragraph now should end with a </p>. If it doesn't, the
			 *  text is not well-formed. In any case, we must remove the </p>.
			 */
			$paragraphs[$index] = str_replace('</p>', '', $paragraphs[$index]);
		}

		return $paragraphs;	
	}
	
	protected function getSections($limit = 0) {
		$section_ids = tx_newspaper::selectRows(
			'uid_foreign',
			'tx_newspaper_article_sections_mm',
			'uid_local = '.$this->getUid(),
			'',
			'',
			$limit? "0, $limit": ''
		);
		
		$sections = array();
		foreach ($section_ids as $id) {
			$sections[] = new tx_newspaper_Section($id['uid_foreign']);
		}
		return $sections;
	}
	
	public function getPrimarySection() {
		$sections = $this->getSections(1);
		return $sections[0];
	}
	
	public function getExtra2PagezoneTable() {
		return self::$extra_2_pagezone_table;
	}

 	/// Ordering function to keep Extras in the order in which they appear on the PageZone
 	/** Supplied as parameter to usort() in getExtras().
 	 *  \param $extra1 first Extra to compare
 	 *  \param $extra2 second Extra to compare
 	 *  \return < 0 if $extra1 comes before $extra2, > 0 if it comes after, 
 	 * 			== 0 if their position is the same 
 	 */
 	static protected function compareExtras(tx_newspaper_ExtraIface $extra1, 
 									 		tx_newspaper_ExtraIface $extra2) {
 		return $extra1->getAttribute('paragraph')-$extra2->getAttribute('paragraph')?
 			$extra1->getAttribute('paragraph')-$extra2->getAttribute('paragraph'):
 			$extra1->getAttribute('position')-$extra2->getAttribute('position');
	}

	static protected $extra_2_pagezone_table = 'tx_newspaper_article_extras_mm';
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	private data members
	//
	////////////////////////////////////////////////////////////////////////////
	
	private $source = null;			///< Source the Article is read from

	private $articleBehavior = null;	///< Object to delegate operations to
	
	///	List of attributes that together constitute an Article
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
