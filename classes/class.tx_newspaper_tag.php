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
 *  \author Oliver Sch�rder <typo3@schroederbros.de>
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
        if ($value) {
            $tag->setAttribute('tag', $value);
        }
        return $tag;
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

        if (!$this->attributes) $this->readAttributesFromDB();

        $where = 'tag = \'' . $this->getAttribute('tag') . '\' AND tag_type = ' . $this->getAttribute('tag_type');
		$result = tx_newspaper::selectRows('uid, tag_type, pid', $this->getTable(), $where);

        if(count($result) > 0) {
            $this->uid = $result[0]['uid'];
        } else {
            $createTime = time();
            $this->setAttribute('crdate', $createTime);
		    $this->setAttribute('tstamp', $createTime);
            $this->setAttribute('pid', tx_newspaper_Sysfolder::getInstance()->getPid($this));
            $this->uid = tx_newspaper::insertRows($this->getTable(), $this->attributes);
        }
        return $this->getUid();
	}

	public function getTitle() {
		global $LANG;
		if (!($LANG instanceof language)) {
			require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
			$LANG = t3lib_div::makeInstance('language');
			$LANG->init('default');
		}
		return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:title_' . $this->getTable(), false);	
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
	
	///	SQL table matching tx_newspaer_Extra s to Control Tags and Tag Zones
	const tag_type_table = 'tx_newspaper_tag_type';
	
	/// Get all control tag types
	/// \return array of control tag type uids	
	public static function getControlTagTypes() {
		$types = array();
		$row = tx_newspaper::selectRows(
			'uid',
			self::tag_type_table,
			'basic_type=2' . tx_newspaper::enableFields(self::tag_type_table) 
		);
		foreach($row as $type) {
			$types[] = $type['uid'];
		}
		return $types;
	}

	/// Get a where part of an sql statement selecting all tag types specified in $tag
	/// \param $tag array with tag type uids
	/// \return where part for an sql statement (bracketed)
	public static function getTagTypesWhere(array $tagTypes) {
		if (!$tagTypes) {
			return ' (1) ';
		}
		$where = '';
		foreach ($tagTypes as $tagType) {
			$where .= (($where)? ' OR ' : '') . 'tag_type=' . $tagType;
		}
		return ' (' . $where . ')';
	}

	/// Get the content tag type 
	/// \return uid of content tags type (there's only one content tag type allowed in the system); or 0 if no content tag type can be found
    public static function getContentTagType() {
		$row = tx_newspaper::selectRows(
			'uid',
			self::tag_type_table,
			'basic_type=1' . tx_newspaper::enableFields(self::tag_type_table) 
		);
		if ($row) {
			return $row[0]['uid']; // there shouldn't be a second content tag type ...
		}
		return 0;
    }	
	
	
	////////////////////////////////////////////////////////////////////////////
	
	private $uid = ''; ///< UID that identifies the tag in the DB
}
