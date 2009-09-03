<?php
/**
 *  \file class.tx_newspaper_article.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_articleiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_extraiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_writeslog.php');

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_articlebehavior.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_smarty.php');

/// An article for the online newspaper
/** The article is the central entity in a newspaper. All other functionalities
 *  deal with displaying articles, lists of articles or additional information
 *  linked to articles.
 * 
 *  Data-wise, an article consists of the minimum set of fields that every
 *  article must have. All additional data connected to an article (e.g. images,
 *  links, tags, media, ...) are called "Extra" and linked to an article. The
 *  class representing Extras is tx_newspaper_Extra and its descendants.
 * 
 *  The Extras must be placed in an Article or in a PageZone. 
 */
class tx_newspaper_Article extends tx_newspaper_PageZone 
	implements tx_newspaper_ArticleIface, tx_newspaper_WritesLog {

	////////////////////////////////////////////////////////////////////////////
	//
	//	magic methods ( http://php.net/manual/language.oop5.magic.php )
	//
	////////////////////////////////////////////////////////////////////////////

	/// Create a tx_newspaper_Article
	/** Initializes the tx_newspaper_ArticleBehavior and tx_newspaper_Smarty
	 *  used as auxiliaries.
	 * 
	 *  Ensures that the current object has a record identifying it in the 
	 *  persistent storage as tx_newspaper_Extra and tx_newspaper_PageZone.
	 */
	public function __construct($uid = 0) {
		$this->articleBehavior = new tx_newspaper_ArticleBehavior($this);
		$this->smarty = new tx_newspaper_Smarty();
		
		if ($uid) {
			$this->setUid($uid);
			
			$this->extra_uid = tx_newspaper_Extra::createExtraRecord($uid, $this->getTable());	
			$this->pagezone_uid = $this->createPageZoneRecord();
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
 		
 		/// clone extras, creating new abstract references to the concrete records
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

	/// \see tx_newspaper_StoredObject
	public function setAttribute($attribute, $value) {
		if (!$this->attributes) {
			$this->attributes = $this->readExtraItem($this->getUid(), $this->getTable());
		}
		
		$this->attributes[$attribute] = $value;
	}

	/// \see tx_newspaper_StoredObject
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
			$this->relateExtra2Article($extra);
		}
		
		return $this->getUid();		
	}

	/// \see tx_newspaper_StoredObject
	public function getUid() { 
		if (!intval($this->uid)) $this->uid = $this->attributes['uid'];
		return intval($this->uid);
	}

	/// \see tx_newspaper_StoredObject
	public function setUid($uid) { 
		$this->uid = $uid;
		if ($this->attributes) {
			$this->attributes['source_id'] = $uid;
			$this->attributes['uid'] = $uid;
		}
	}

	/// \see tx_newspaper_StoredObject
	public public function getTable() {
		return 'tx_newspaper_article';
	}

	/// \see tx_newspaper_StoredObject
	static public function getModuleName() {
		return 'np_article';
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_ExtraIface
	//
	////////////////////////////////////////////////////////////////////////////
		
	/// Renders an article with all of its Extras
	/** \param $template_set Template set to use
	 */
	public function render($template_set = '') {

		/// Default articles should never contain text that is displayed.
		if ($this->getAttribute('is_template')) return;
		
		/** Check whether to use a specific template set.
		 *	This must be done regardless if this is a template used to define
		 *	default placements for articles, or an actual article
		 */
		if ($this->getAttribute('template_set')) {
			$template_set = $this->getAttribute('template_set');
		}
		
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

		/** Assemble the text paragraphs and extras in an array of the form:
		 *  \code
		 *  array(
		 *  	$paragraph_number => array(
		 * 			"text" => $text_of_paragraph,
		 *    		"spacing" => {0, 1, 2, ...},             // fuer leere absaetze hinterm text
		 *          "extras" => array(
		 *				$position => array(
         *					"extra_name" => get_class(),
         * 					"content" => $rendered_extra
       	 *				),
		 * 		 		...
		 * 			)
		 * 		),
		 * 		...
		 *  )
		 *  \endcode
		 */
		$text_paragraphs = $this->splitIntoParagraphs();
		$paragraphs = array();
		$spacing = 0;
		foreach ($text_paragraphs as $index => $text_paragraph) {

			$paragraph = array();
			if (trim($text_paragraph)) {
				$paragraph['text'] = $text_paragraph;
				$paragraph['spacing'] = intval($spacing);
				$spacing = 0;
				foreach ($this->getExtras() as $extra) {
					if ($extra->getAttribute('paragraph') == $index ||
						sizeof($text_paragraphs)+$extra->getAttribute('paragraph') == $index) {
						$paragraph['extras'][$extra->getAttribute('position')] = array();
						$paragraph['extras'][$extra->getAttribute('position')]['extra_name'] = $extra->getTable();
						$paragraph['extras'][$extra->getAttribute('position')]['content'] .= $extra->render($template_set);
					}
				}
				/*  Braindead PHP does not sort arrays automatically, even if
				 *  the keys are integers. So if you, e.g., insert first $a[4]
				 *  and then $a[2], $a == array ( 4 => ..., 2 => ...).
				 *  Thus, you must call ksort.
				 */
				if ($paragraph['extras']) ksort($paragraph['extras']);
				$paragraphs[] = $paragraph;
			} else {
				//	empty paragraph, increase spacing value to next paragraph
				$spacing++;
			}
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

#t3lib_div::devlog('$paragraphs', 'newspaper', 0, $paragraphs);
		$this->smarty->assign('paragraphs', $paragraphs);
		$this->smarty->assign('attributes', $this->attributes);
		$ret = $this->smarty->fetch($this);

		return $ret;
	}

	/// Read data from table \p $table with UID \p $uid
	/** \param $uid UID of the record to read
	 *  \param $table SQL table to read record from
	 *  \return The data contained in the requested record
	 *  \todo remove.
	 */ 
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

	/// Get the list of Extra s associated with this Article in sorted order
	/** The Extras are sorted by attribute <tt>paragraph</tt> first and
	 *  <tt>position</tt> second.
	 */
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
					$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
		
		usort($this->extras, array(get_class($this), 'compareExtras')); 
		
		return $this->extras; 
	}
	
	/// Find the first tx_newspaper_Extra of a given type
	/** \param $extra_class The desired type of Extra, either as object or as
	 *  	class name
	 *  \return The first Extra of the given class (by appearance in article),
	 * 		or null.
	 */
	public function getExtra($extra_class) {

		if ($extra_class instanceof tx_newspaper_Extra) {
			$extra_class = tx_newspaper::getTable($extra_class);
		}
		
		foreach ($this->getExtras() as $extra) {
			if (tx_newspaper::getTable($extra) == strtolower($extra_class)) {
				return $extra;
			}
		}
		
		return null;
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	class tx_newspaper_PageZone
	//
	////////////////////////////////////////////////////////////////////////////

	/// Get the tx_newspaper_PageZoneType associated with this Article
	/** \return The tx_newspaper_PageZoneType associated with this Article. If
	 * 		this is not the one where Attribute \p is_article is set, there
	 * 		is something weird going on.
	 *  \todo Check for \p is_article. 
	 */
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
	
	/// Delete all Extras
	public function clearExtras() {
		$this->extras = array();	
	}

	/// Write record in MM table relating an Extra to this article
	/** The MM table record is only written if it did not exist beforehand.
	 * 
	 *  If \p $extra did not have a record in the abstract Extra table
	 *  (<tt>tx_newspaper_extra</tt>), the record is created.
	 * 
	 *  The MM-table <tt>tx_newspaper_article_extra_mm</tt> will contain an
	 *  association between the Article's UID and the UID in the abstract Extra
	 *  table.
	 * 
	 *  \param $extra The tx_newspaper_Extra to add to \p $this.
	 * 
	 *  \return The UID of \p $extra in the abstract Extra table.
	 */	
	public function relateExtra2Article(tx_newspaper_ExtraIface $extra) {
		
		$extra_table = tx_newspaper::getTable($extra);
		$extra_uid = $extra->getUid();
		$article_uid = $this->getUid();

		$abstract_uid = $extra->getExtraUid();
		if (!$abstract_uid)	$abstract_uid = tx_newspaper_Extra::createExtraRecord($extra_uid, $extra_table); 
		
		/// Write entry in MM table (if not exists)
		$row = tx_newspaper::selectZeroOrOneRows(
			'uid_local, uid_foreign', 
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
	
	/// Change the paragraph of an Extra recursively on inheriting Articles
	/** This function is used to change the paragraph of an Extra on Articles
	 *  that serve as templates for the placement of Articles in Sections
	 *  ("Default Articles"). Because the <tt>paragraph</tt> attribute must be
	 *  changed in the default articles of Sections that inherit from the 
	 *  current Section, this operation is non-trivial and cannot be performed
	 *  by a simple setAttribute().
	 * 
	 *  \p $extra is moved to the first position in the new paragraph, because
	 * 	otherwise the operation would result in a random position. Extras
	 *  already present in the target paragraph might have to be moved. That
	 *  adds further complications to this functions.
	 * 
	 *  \todo Replace with a generic function to set attributes recursively.
	 * 
	 *  \param $extra tx_newspaper_Extra which should be moved to another
	 * 		paragraph.
	 *  \param $new_paragraph The paragraph to which \p $extra is moved.
	 */
	public function changeExtraParagraph(tx_newspaper_Extra $extra, $new_paragraph) {
		$paragraph = intval($extra->getAttribute('paragraph'));
		if ($paragraph != intval($new_paragraph)) {
			$extra->setAttribute('paragraph', intval($new_paragraph));
			$extra->setAttribute('position', $this->getInsertPosition(0));
			$extra->store();
			
			/** change the paragraph in inheriting page zones too
			 *  \todo optional: only overwrite paragraph in inheriting pagezones
			 *  if it has not been changed manually there.
			 */
			foreach($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
				if (!($inheriting_pagezone instanceof tx_newspaper_Article)) {
					/* Probably no harm can come if the page zone is not an 
					 * Article. So we just write a message to the devlog and 
					 * skip it.
					 */
					t3lib_div::devlog(
						'Weird: There\'s a PageZone inheriting from an Article which is not itself an Article',
						'newspaper', 0,
						array(
							'parent page zone' => $this,
							'inheriting page zone' => $inheriting_pagezone
						)
					);
					continue;
				}
				$copied_extra = $inheriting_pagezone->findExtraByOriginUID($extra->getOriginUid());
				if ($copied_extra) $inheriting_pagezone->changeExtraParagraph($copied_extra, $new_paragraph);
			}
		}
	}
	
	/// Generate a URL which links to the Article on the correct Page
	/** \param $section Section from which the link is generated
	 *  \param $pagetype PageType of the wanted page
	 *  \todo Handle links to articles in sections other than their primary section
	 *  \todo Handle PageType other than article page
	 *  \todo Check if target page has an Article display Extra 
	 */
	public function getLink(tx_newspaper_Section $section = null, 
							tx_newspaper_PageType $pagetype = null) {
		if ($section) {
			throw new tx_newspaper_NotYetImplementedException(
				'Links to articles in sections other than their primary section' .
				' (uid ' . $this->getUid() . ')'
			);
		}
		$section = $this->getPrimarySection();
		if (!$section instanceof tx_newspaper_Section) {
			throw new tx_newspaper_NotYetImplementedException(
				'Links to articles with no primary section' .
				' (uid ' . $this->getUid() . ')'
			);
			
		}
		else {
			$typo3page = $section->getTypo3PageID();	
		}
		
		return tx_newspaper::typolink_url(
			array(
				'id' => $typo3page,
				tx_newspaper::article_get_parameter => $this->getUid()
		));
	}
	
	/// Get a list of all attributes in the tx_newspaper_Article table.
	/** \return All attributes in the tx_newspaper_Article table.
	 */
	static public function getAttributeList() { return self::$attribute_list; }
	
	
	/// gets a list of tx_newspaper_Article objects assigned to given article type
	/** \param $at tx_newspaper_ArticleType object
	 *  \param $limit max number of records to read (default: 10), if negative no limit is used
	 *  \return array with tx_newspaper_Article objects 
	 */
	static public function listArticlesWithArticletype(
		tx_newspaper_ArticleType $at, $limit = 10
	) {

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

	/// adds sections to article (will be inserted after existing sections)
	/** The Article is listed in tx_newspaper_Section \p $s afterwards.
	 * 
	 *  \param $s New Section
	 */
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

	/// Get the primary tx_newspaper_Section of an Article
	/** \return The Section in which \p $this is displayed by default, if no
	 * 		Section context is given. 
	 */		
	public function getPrimarySection() {
		$sections = $this->getSections(1);
		return $sections[0];
	}
	
	/// Get the SQL table which associates tx_newspaper_Extra s with Page Zones.
	public function getExtra2PagezoneTable() {
		return self::$extra_2_pagezone_table;
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	protected functions
	//
	////////////////////////////////////////////////////////////////////////////
	
	/// Find out which tx_newspaper_Page is currently displayed
	/** Uses $_GET to find out which tx_newspaper_PageType is requested on the
	 *  current tx_newspaper_Section.
	 * 
	 *  \return The currently displayed tx_newspaper_Page.
	 */
	protected function getCurrentPage() {
		$section = tx_newspaper::getSection();
		return new tx_newspaper_Page($section, new tx_newspaper_PageType($_GET));
	}
	
	/// Split the text into an array, one entry for each paragraph
	/** The functionality of this function depends on the way the RTE stores
	 *  line breaks. Currently it breaks the text at "<p>/</p>"-pairs and also
	 *  at line breaks ("\n").
	 * 
	 *  \attention If the format of line breaks changes, this function must be
	 * 	altered.
	 */
	protected function splitIntoParagraphs() {
		/** A text usually starts with a <p>, in that case the first paragraph
		 *  must be removed. It may not be the case though, if so, the first
		 *  paragraph is meaningful and must be kept.
		 */
		$temp_paragraphs = explode('<p', $this->getAttribute('text'));
		$paragraphs = array();		
		
		foreach ($temp_paragraphs as $paragraph) {
			/// remove the test of the <p>-tag from every line
			$paragraph = trim(substr($paragraph, strpos($paragraph, '>')+1));
			/** each paragraph now should end with a </p>. If it doesn't, the
			 *  text is not well-formed. In any case, we must remove the </p>.
			 */
			$paragraph = str_replace('</p>', '', $paragraph);
			
			/// Now we split the paragraph at line breaks.
			$sub_paragraphs = explode("\n", $paragraph);
			
			/// Store the pieces in one flat array
			foreach($sub_paragraphs as $sub_paragraph) $paragraphs[] = $sub_paragraph;
		}

		return $paragraphs;	
	}
	
	/// Get the list of tx_newspaper_Section s to which the current article belongs
	/** \param $limit Maximum number of Sections to find
	 *  \return List of tx_newspaper_Section s to which the current article belongs
	 */
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
	
	
	/// Get the index of the provided tx_newspaper_Extra in the Extra array
	/** Binary search for an Extra, assuming that \p $this->extras is ordered by
	 *  paragraph first and position second.
	 * 
	 *  \param $extra tx_newspaper_Extra to find
	 *  \return Index of \p $extra in \p $this->extras
	 *  \throw tx_newspaper_InconsistencyException if \p $extra is not present
	 * 		in \p $this->extras
	 */ 
	protected function indexOfExtra(tx_newspaper_Extra $extra) {
        $high = sizeof($this->getExtras())-1;
        $low = 0;
       
        while ($high >= $low) {
            $index_to_check = floor(($high+$low)/2);
            $comparison = $this->getExtra($index_to_check)->getAttribute('paragraph') -
            			  $extra->getAttribute('paragraph');
            if ($comparison < 0) $low = $index_to_check+1;
            elseif ($comparison > 0) $high = $index_to_check-1;
            else {
	            $comparison = $this->getExtra($index_to_check)->getAttribute('position') -
	            			  $extra->getAttribute('position');
	            if ($comparison < 0) $low = $index_to_check+1;
	            elseif ($comparison > 0) $high = $index_to_check-1;
	            else return $index_to_check;
            }
        }
		
		// Loop ended without a match
		throw new tx_newspaper_InconsistencyException('Extra ' . $extra->getUid() .
													  ' not found in array of Extras!');		
	}
	

 	/// Ordering function to keep Extras in the same order as they appear on the PageZone
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

	/// SQL table which associates tx_newspaper_Extra s with Page Zones
	static protected $extra_2_pagezone_table = 'tx_newspaper_article_extras_mm';
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	private data members
	//
	////////////////////////////////////////////////////////////////////////////
	
	private $source = null;			///< Source the Article is read from

	private $articleBehavior = null;	///< Object to delegate operations to
	
	///	List of attributes that together constitute an Article
	/** \todo update */
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
	 * 
	 * \todo update
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