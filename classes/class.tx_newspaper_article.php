<?php
/**
 *  \file class.tx_newspaper_articleimpl.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_articleiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_extraiface.php');

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
		$this->smarty = new tx_newspaper_Smarty();
		
		if ($uid) {
			$this->setUid($uid);
			
			/** \todo I'm not sure whether the following line should remain. It's a
			 *  safety net because currently it's not ensured that extras are 
			 *  created consistently.
			 */
			$this->extra_uid = tx_newspaper_Extra::createExtraRecord($uid, $this->getTable());	
			$this->pagezone_uid = tx_newspaper_PageZone::createPageZoneRecord($uid, $this->getTable());
			$this->smarty->setTemplateSearchPath(
				array(
					'template_sets/' . strtolower($this->getPageZoneType()->getAttribute('name')),
					'template_sets'
				)
			);
		}
		
	}

	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_InSysFolder
	//
	////////////////////////////////////////////////////////////////////////////

	public function getUid() { return intval($this->uid); }

	public function setUid($uid) { 
		$this->uid = $uid;
		 $this->setAttribute('source_id', $uid);
	}

	public public function getTable() {
		return 'tx_newspaper_article';
	}

	static public function getModuleName() {
		return 'np_article';
	}

	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_ExtraIface
	//
	////////////////////////////////////////////////////////////////////////////
	
	/// Renders an article
	public function render($template = '') {

		if ($this->getAttribute('is_template')) {
			/** Handle case where $this is a placeholder for an actual article
			 *  (formerly Extra_ArticleRenderer)
			 */
			$ret = '';
			$article = new tx_newspaper_article(t3lib_div::_GP('art'));
			$ret = $article->render();
		} else {
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
			 * 			"extras" => array(
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
						$paragraph['extras'][$extra->getAttribute('position')] .= $extra->render();
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
					$paragraphs[0]['extras'][] = $extra->render();
				} else if ($extra->getAttribute('paragraph') > sizeof($text_paragraphs)) {
					$paragraphs[sizeof($paragraphs)-1]['extras'][] = $extra->render();
				}
			}

			$this->smarty->assign('paragraphs', $paragraphs);
			$ret = $this->smarty->fetch($this);
		} 
		return $ret;
	}

	/// returns an actual member (-> Extra)
	public function getAttribute($attribute) {
				
		if (!$this->attributes) {
			$this->attributes = $this->readExtraItem($this->getUid(), $this->getTable());
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

	/** \todo Internationalization */
	static public function getTitle() {
		return 'Article';
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
		return $this->extras; 
	}

	public function addExtra(tx_newspaper_Extra $newExtra) {
		throw new tx_newspaper_NotYetImplementedException();
	}

	////////////////////////////////////////////////////////////////////////////
	//
	//	class tx_newspaper_Article
	//
	////////////////////////////////////////////////////////////////////////////
	
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

	////////////////////////////////////////////////////////////////////////////
	
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
	
	protected function getPrimarySection() {
		$sections = $this->getSections(1);
		return $sections[0];
	}
	
	protected function getExtra2PagezoneTable() {
		return self::$extra_2_pagezone_table;
	}

	static protected $extra_2_pagezone_table = 'tx_newspaper_pagezone_article_extras_mm';
	
	////////////////////////////////////////////////////////////////////////////
	
	private $source = null;			///< Source the Article is read from
	private $uid = '';				///< UID that identifies the article in the source
	
	private $extra_uid = null;		///< article's UID in the abstract Extra table
	private $pagezone_uid = null;	///< article's UID in the abstract PageZone table

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
