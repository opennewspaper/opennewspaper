<?php
/**
 *  \file class.tx_newspaper_tag.php
 *
 *  This file is part of the TYPO3 extension "newspaper".
 *
 *  Copyright notice
 *
 *  (c) 2008 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *
 *  \author Oliver Schroeder <typo3@schroederbros.de>
 *  \date Mar 25, 2009
 */

/// Tag
/** \todo document me!
 */
class tx_newspaper_Tag implements tx_newspaper_StoredObject {

	public function __construct($uid = 0) {
		$uid = intval($uid);
		if ($uid > 0) {
			$this->setUid($uid);
		}
	}

    /// Creates a new content tag
    /// \return tx_newspaper_tag object
    public static function createContentTag($value = null) {
        $tag = new tx_newspaper_Tag();
        $tag->setAttribute('tag_type', self::getContentTagType());
        $tag->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
        if ($value) {
            $tag->setAttribute('tag', $value);
        }
        return $tag;
    }


    /// Creates a new control tag
    /**
     * \param $controlTagCat uid of control tag category record
     * \param $tag name of tag
     * \param $title name of dossier etc. associated with this control tag
     * \param $section uid of section associated with this control tag
     * \return tx_newspaper_tag object
     */
    public static function createControlTag($controlTagCat, $tag, $title='', $section=0) {
        $newTag = new tx_newspaper_tag();
        $newTag->setAttribute('tag_type', self::getControlTagType());
        $newTag->setAttribute('ctrltag_cat', intval($controlTagCat));
        $newTag->setAttribute('tag', $tag);
        $newTag->setAttribute('title', $title);
        $newTag->setAttribute('section', intval($section));
        $newTag->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
        return $newTag;
    }

    /// \return Array with all control tag categories
    public static function getAllControltagCategories() {
    	return tx_newspaper::selectRows(
    		'uid,title',
    		self::ctrltag_cat_table,
    		'1' . tx_newspaper::enableFields(self::ctrltag_cat_table),
    		'',
    		'sorting'
    	);
    }

    /// \return Array with all control tags for given $category
    public static function getAllControlTags($category) {
    	$category = intval($category);
    	$rows = tx_newspaper::selectRows(
    		'uid',
    		self::tag_table,
    		'tag_type=' . self::getControltagType() . ' AND ctrltag_cat=' . $category  . tx_newspaper::enableFields(self::tag_table),
    		'',
    		'tag'
    	);
    	$tags = array();
    	foreach($rows as $row) {
    		$tags[] = new tx_newspaper_tag($row['uid']);
    	}
    	return $tags;
    }


	/// \return Array with all content tags
    public static function getAllContentTags() {
    	$rows = tx_newspaper::selectRows(
    		'uid',
    		self::tag_table,
    		'tag_type=' . self::getContentTagType() . tx_newspaper::enableFields(self::tag_table),
    		'',
    		'tag'
    	);
    	$tags = array();
    	foreach($rows as $row) {
    		$tags[] = new tx_newspaper_tag($row['uid']);
    	}
    	return $tags;
    }

	/**
	 * The $tagToBeMerged is merged with the current tag
	 * The $tagToBeMerged is set to null
	 * \param tx_newspaper_tag $tagToBeMerged
	 * \return true, if merge was successful, else false
	 * \todo: tx_newspaper_extra_image_tags_mm if tag are to be used in images
	 */
    public function merge(tx_newspaper_tag &$tagToBeMerged) {

    	if ($this->getUid() == $tagToBeMerged->getUid()) {
    		return false; // no need to merge into itself ...
    	}

    	// remove tag relations where this tag AND target tag are attached ()would create a duplicate entry otherwise)

    	// get article uids with this (=target) tag attached
		$articleUids = tx_newspaper::selectRows(
			'uid_local',
			'tx_newspaper_article_tags_mm',
			'uid_foreign=' . $this->getUid()
		);
		// make array accessible for explode ...
		for($i = 0; $i < sizeof($articleUids); $i++) {
			$articleUids[$i] = $articleUids[$i]['uid_local'];
		}
//t3lib_div::devlog('tag::merge() get articles', 'newspaper', 0, array('articleUids' => $articleUids, 'q' => tx_newspaper::$query));

		// remove tag to be merged from articles where this (=target) tag has already been attached
		if ($articleUids) {
			tx_newspaper::deleteRows(
				'tx_newspaper_article_tags_mm',
				'uid_foreign=' . $tagToBeMerged->getUid() . ' AND uid_local IN (' . implode(',', $articleUids) . ')'
			);
		}
//t3lib_div::devlog('tag::merge() delete duplicates', 'newspaper', 0, array('q' => tx_newspaper::$query));


    	// move article tag relation to current tag (from tag to be merged)
		tx_newspaper::updateRows(
			'tx_newspaper_article_tags_mm',
			'uid_foreign=' . $tagToBeMerged->getUid(),
			array('uid_foreign' => $this->getUid())
		);
//t3lib_div::devlog('tag::merge() merge tag', 'newspaper', 0, array('q' => tx_newspaper::$query));

		// delete tag to be merged
		if (!tx_newspaper::updateRows(
			'tx_newspaper_tag',
			'uid=' . $tagToBeMerged->getUid(),
			array('deleted' => 1)
		)) {
			return false;
		}
//t3lib_div::devlog('tag::merge() delete tag to be merged', 'newspaper', 0, array('q' => tx_newspaper::$query));
		$tagToBeMerged = null; // set tag to null, this tag does not exist anymore

// \todo: call dependecy tree, see #1536

    	return true;
    }


    /// \return Array with all tag zones (key = tag zone uid)
    public static function getAllTagZones() {
    	$row = tx_newspaper::selectRows(
    		'uid,name',
    		self::tagzone_table,
    		'1' . tx_newspaper::enableFields(self::tagzone_table),
    		'',
    		'name'
    	);
    	$tagzones = array();
    	for ($i = 0; $i < sizeof($row); $i++) {
			$tagzones[$row[$i]['uid']] = $row[$i];
    	}
    	return $tagzones;
    }

    /// \return Name of tag zone for given $uid
    public static function getTagZoneName($uid) {
    	$uid = intval($uid);
    	$row = tx_newspaper::selectRows(
    		'name',
    		self::tagzone_table,
    		'uid=' . $uid . tx_newspaper::enableFields(self::tagzone_table)
    	);
		if ($row) {
    		return $row[0]['name'];
		} else {
			return '';
		}
    }

    /// \return Name of tag zone for given $uid
    public function getCategoryName() {
    	$row = tx_newspaper::selectRows(
    		'title',
    		'tx_newspaper_ctrltag_category',
    		'uid=' . $this->getAttribute('ctrltag_cat') . tx_newspaper::enableFields('tx_newspaper_ctrltag_category')
    	);
    	if ($row) {
    		return $row[0]['title'];
		} else {
			return '';
		}
	}


    /// \param $tz_uid uid of taz zone
    /// \return Array with Extras assigned to tag zone identified by $tz_uid
    public function getTagzoneExtras($tz_uid) {
    	$rows = tx_newspaper::selectRows(
    		'extra',
    		self::ctrltag_to_extra,
    		'tag=' . $this->getUid() . ' AND tag_zone=' . intval($tz_uid) . tx_newspaper::enableFields(self::ctrltag_to_extra),
    		'',
    		'sorting'
    	);
    	$extras = array();
    	foreach($rows as $row) {
    		$extras[] = tx_newspaper_Extra_Factory::getInstance()->create($row['extra']);
    	}
    	return $extras;
    }

    /**
     *  \param $extra Extra object
     *  \param $tz_uid uid of tag zone
     *  \return true id record was written else false
     */
    public function addExtraToTagzone(tx_newspaper_extra $extra, $tz_uid) {
    	$tz_uid = intval($tz_uid);
    	if ($tz_uid) {
    		$uid = tx_newspaper::insertRows(self::ctrltag_to_extra, array(
				'tag' => $this->getUid(),
				'extra' => $extra->getExtraUid(),
				'tag_zone' => $tz_uid,
				'pid' => tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_tag()),
				'crdate' => time(),
				'tstamp' => time(),
				'cruser_id' => tx_newspaper::getBeUserUid()
			));
			return true;
    	}
    	return false;
    }

	/**
	 * Fetches all articles assigned to this tag
	 * \param $limit Limits the number of articles (default is -1 = no limit)
	 * \return Array with articles
	 */
    public function getArticles($limit=-1) {
		$select_method_strategy = SelectMethodStrategy::create(true);
    	$results = tx_newspaper::selectRows(
			$select_method_strategy->fieldsToSelect(),
			'tx_newspaper_article, tx_newspaper_article_tags_mm',
			'tx_newspaper_article_tags_mm.uid_foreign=' . $this->getUid() . ' AND tx_newspaper_article.uid=tx_newspaper_article_tags_mm.uid_local',
			'',
			'',
			($limit > 0)? $limit : ''
		);

		$articles = array();
		foreach ($results as $row) {
            $articles[] = $select_method_strategy->createArticle($row);
		}

		return $articles;
    }

    /**
     * Gets the section associated to this control tag
     * \return Section object for control tag, false, if content tag
     */
    public function getSection() {
		if ($this->getAttribute('tag_type') != self::getControlTagType()) {
			return false;
		}
		return new tx_newspaper_section($this->getAttribute('section'));
    }


    /**
     * Checks if given title is unique for control tags (always true for content tags)
     * @param $title Title to check for uniqueness
     * @return true if title is unique, else false
     */
    public function isTitleUnique($title) {
    	$rows = tx_newspaper::selectRows(
    		'*',
    		self::tag_table,
    		'title LIKE "' . $GLOBALS['TYPO3_DB']->fullQuoteStr($title, self::tag_table) . '" AND ' . '
    			uid<>' . $this->getUid() . ' AND ' .
    			'tag_type=' . self::getControlTagType() .
    			tx_newspaper::enableFields(self::tag_table)
    	);
		return (sizeof($rows) == 0);
    }


	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		try {
			return $this->getAttribute('tag');
		} catch (tx_newspaper_Exception $e) {
			return 'Unstored tx_newspaper_Tag object';
		}
	}

	public function getAttribute($attribute) {
		/// Read Attributes from persistent storage on first call
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
				'*',
				tx_newspaper::getTable($this),
				'uid=' . $this->getUid() . tx_newspaper::enableFields(tx_newspaper::getTable($this))
			);
		}

		if (!array_key_exists($attribute, $this->attributes)) {
			throw new tx_newspaper_WrongAttributeException($attribute);
		}
		return $this->attributes[$attribute];
	}

	/** No tx_newspaper_WrongAttributeException here. We want to be able to set
	  *  attributes, even if they don't exist beforehand.
	  */
	public function setAttribute($attribute, $value) {
		if (!$this->attributes && $this->getUid()) {
			$this->attributes = tx_newspaper::selectOneRow(
					'*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
			);
		}

		$this->attributes[$attribute] = $value;
	}

	/**
     * Stores a tag and prevents duplicate tags by checking content and type.
     * @return uid
     * @throws tx_newspaper_IllegalUsageException
     */
	public function store() {
        if(!$this->getAttribute('tag') || trim($this->getAttribute('tag')) === '' || !$this->getAttribute('tag_type') ) {
            $message = '[tag: \''.$this->getAttribute('tag') .'\', type: \'' . !$this->getAttribute('tag_type') .'\]';
            $message = 'Can not store tag, it has no content or type. '.$message;
            throw new tx_newspaper_IllegalUsageException($message);
        }

		if(self::getControlTagType() === intval($this->getAttribute('tag_type'))) {
			if(!$this->getAttribute('ctrltag_cat')) {
				throw new tx_newspaper_IllegalUsageException('[tag: \''.$this->getAttribute('tag').'\' has no category');
			}
		}

		if (!$this->attributes) $this->readAttributesFromDB();

		// check if tag has been stored in the database already
        $where = 'tag = \'' . $this->getAttribute('tag') . '\' AND tag_type = ' . $this->getAttribute('tag_type');
		if($this->getAttribute('tag_type') === self::getControlTagType()) {
			$where .= ' AND ctrltag_cat = ' . intval($this->getAttribute('ctrltag_cat'));
		}
		$result = tx_newspaper::selectRows('*', $this->getTable(), $where);

        if(count($result) > 0) {
			// process existing tag
            $this->setUid($result[0]['uid']); // record is already stored in db, so get uid of taht record

            // check if data was updated
			$data = array_diff($this->attributes, $result[0]);
			if ($data) {
				// record needs to be update ...
				$data['tstamp'] = time(); // ... so update tstamp
				tx_newspaper::updateRows($this->getTable(), 'uid=' . $this->getUid(), $data); // and store data
			}
        } else {
        	// store new tag
            $createTime = time();
            $this->setAttribute('crdate', $createTime);
		    $this->setAttribute('tstamp', $createTime);
            $this->setAttribute('pid', tx_newspaper_Sysfolder::getInstance()->getPid($this));
            $this->setUid(tx_newspaper::insertRows($this->getTable(), $this->attributes));
        }
        return $this->getUid();
	}

	public function getTitle() {
		return tx_newspaper::getTranslation('title_' . $this->getTable());
	}

	public function getUid() {
		return intval($this->uid);
	}

	public function setUid($uid) {
		$this->uid = $uid;
	}

	/// \return Name of the database table the object's data are stored in
	public function getTable() { return tx_newspaper::getTable($this); }

	static public function getModuleName() { return 'np_tag'; }


	/// Get all tag zones (array with uids) this tag is assigned to
	public function getTagZones() {
    	return tx_newspaper::selectRows(
    		'DISTINCT tag_zone',
    		self::ctrltag_to_extra,
    		'tag=' . $this->getUid() . tx_newspaper::enableFields(self::ctrltag_to_extra)
    	);
	}

	/// Given a partial tag, return all possible completions for that tag
	/** \param $fragment A string to interpret as a part of a tag
	 *  \param $max Maximum number of hints returned
	 *  \param $start_only If \c true, returns only tags beginning with
	 *  	\p $fragment
	 *  \param $strong If \c true, fatten the requested portion of the tag that
	 * 		has been searched for.
	 *  \return Array of tags (UIDs as key and tagname as value) that match \p $fragment
	 */
	static public function getCompletions($fragment,
										  $max = 0,
										  $start_only = false,
										  $strong = false) {
		$results = tx_newspaper::selectRows(
			'uid, tag', 'tx_newspaper_tag',
			'tag LIKE \'' . ($start_only? '': '%') . $fragment . '%\'',
			'', '', ($max? ('0, ' . $max): '')
		);

		$return = array();

		if ($results) {
			foreach ($results as $row) {
				$return[$row['uid']] = str_replace($fragment,
										($strong? '<strong>': '') . $fragment . ($strong? '</strong>': ''),
										$row['tag']);
			}
		}

		return $return;
	}


	////////////////////////////////////////////////////////////////////////////
	/// Tag type handling


	/// SQL table storing tags
	const tag_table = 'tx_newspaper_tag';

	/// SQL table storing control tag categories
	const ctrltag_cat_table = 'tx_newspaper_ctrltag_category';

	/// SQL table storing tag zones
	const tagzone_table = 'tx_newspaper_tag_zone';

	/// SQL table assigning control tags and extras to tag zones
	const ctrltag_to_extra = 'tx_newspaper_controltag_to_extra';



	/// Get control tag type
	/// \return 2 hard coded
	public static function getControlTagType() {
		return 2; // hard coded
	}

	/// Checks if a tag title and control tag category combination is already in use
	/** \param $tag Tag title
	 *  \param $ctrltagtype uid of control tag type
	 *  \return true if control tag $tag is already stored in the database for given $ctrltagtype
	 */
	public static function doesControlTagAlreadyExist($tag, $ctrltagtype) {
		$row = tx_newspaper::selectZeroOrOneRows(
			'uid',
			'tx_newspaper_tag',
			'tag="' . $tag . '" AND tag_type=' . self::getControlTagType().
			' AND ctrltag_cat = '.intval($ctrltagtype)
		);
		return (isset($row['uid']) && $row['uid'] > 0);
	}


	/// Get the content tag type
	/// \return 1 (hard coded)
    public static function getContentTagType() {
		return 1; // hard coded
    }


	////////////////////////////////////////////////////////////////////////////

	private $uid = ''; ///< UID that identifies the tag in the DB
}
