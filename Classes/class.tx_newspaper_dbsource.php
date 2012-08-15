<?php
/**
 *  \file class.tx_newspaper_dbsource.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_source.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_articleiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_extraiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_sourcebehavior.php');

/// A Source which reads articles from a SQL DB
class tx_newspaper_DBSource implements tx_newspaper_Source {

	public function __construct() {
		$this->sourceBehavior = new tx_newspaper_SourceBehavior($this);
	}

	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		return get_class($this) . '-object ' . "\n";
	}

	/// Reads ONE field for the given Extra
	public function readField(tx_newspaper_ExtraIface $extra, $field, tx_newspaper_SourcePath $uid) {
        $row = tx_newspaper::selectOneRow(
        	$extra->mapFieldToSourceField($field, $this),
			$extra->sourceTable($this),
			"uid = ".intval($uid->getID())
		);

		$value = $row[$extra->mapFieldToSourceField($field, $this)];		

		$extra->setAttribute($field, $value);
	}

	/// Reads the specified fields of the article with the specified UID
	public function readFields(tx_newspaper_ExtraIface $extra, array $fieldList, tx_newspaper_SourcePath $uid) {
		$this->sourceBehavior->readFields($extra, $fieldList, $uid);
	}

	/// Creates and reads a full article with the specified UID
	public function readArticle($articleclass, tx_newspaper_SourcePath $uid) {
		/** \todo Factor out the code to check the class into SourceBehavior and
		 *  call that one. Also from SourceBehavior::readArticle().
         *  @var tx_newspaper_Article $article
		 */
		$article = null;
		
		/// $article is set to an object of an appropriate class
		if (is_a($articleclass, 'tx_newspaper_ArticleIface')) {
			$article = $articleclass;
			$articleclass = get_class($article);	// to throw meaningful exception
		} else {
			if (class_exists($articleclass)) $article = new $articleclass;
			else throw new tx_newspaper_WrongClassException($articleclass);
		}
		
		/// If that didn't work, throw up
		if (!is_a($article, 'tx_newspaper_ArticleIface')) {
			throw new tx_newspaper_WrongClassException($articleclass);
		}
		
		/// Read all fields which are stored in the DB
		/** \todo ...or should I read just those which are defined in
		 * 		  self::$attribute_list? Which is better?
		 */
        try {
	        $row = tx_newspaper::selectOneRow(
				'*',
				$article->sourceTable($this),
				"uid = ".intval($uid->getID())
			);
        } catch (tx_newspaper_DBException $e) {
        	throw new tx_newspaper_WrongClassException();
        }
        /// \todo ...or loop over self::$attribute_list? Which is better?
        if ($row) foreach ($row as $field => $value) {
        	/** Set the attribute of \p $article connected with the DB field
        	 *  \p $field 
        	 * 
        	 *  If \p $field does not map to an attribute of \p $article, we
        	 *  must catch a tx_newspaper_IllegalUsageException
        	 * 
        	 *  \todo Still, the question: What to do if there are fields in the
        	 * 		  DB which are not in self::$attribute_list? 
        	 */
        	try {		 
				$article->setAttribute($this->mapSourceFieldToField($article,
																	$field), 
									   $value);
        	} catch (tx_newspaper_IllegalUsageException $e) {
        		//  nothing to do!
        	}
		}

		/// And tell the Article the truth: "I'm your father, Luke"
		$article->setSource(array($this));
		$article->setUid($uid->getID());
						 
		return $article;
	}

	/// Reads an array of articles with the specified UIDs
	public function readArticles($articleclass, array $uids) {
		return  $this->sourceBehavior->readArticles($articleclass, $uids);
	}

	/// Reads array of only the specified fields of articles with the given UIDs 
	public function readPartialArticles($articleclass,array $fields, array $uids) {
		throw new tx_newspaper_NotYetImplementedException();
	}

    /// reads an extra (-> Source)
    public function readExtra($extraclass, tx_newspaper_SourcePath $uid) {
		throw new tx_newspaper_NotYetImplementedException();
    }

    /// reads an array of extras (-> Source)
    public function readExtras($extraclass, array $uids) {
		throw new tx_newspaper_NotYetImplementedException();
    }

    public function writeArticle(tx_newspaper_ArticleIface $article, tx_newspaper_SourcePath $uid) {
    	throw new tx_newspaper_NotYetImplementedException();
    }
    
    public function writeExtra(tx_newspaper_ExtraIface $extra, tx_newspaper_SourcePath $uid) {
    	throw new tx_newspaper_NotYetImplementedException();
    }
    
    public function browse(tx_newspaper_SourcePath $path) {
    	throw new tx_newspaper_NotYetImplementedException();
    }
    
    public function getPathSegments(tx_newspaper_SourcePath $path) {
    	throw new tx_newspaper_NotYetImplementedException();
    }

	public function getTitle () {
    	throw new tx_newspaper_NotYetImplementedException();
	}

	public function getProductionStatus(tx_newspaper_SourcePath $path) {
        throw new tx_newspaper_NotYetImplementedException();
	}
	
	////////////////////////////////////////////////////////////////////////////
	//		end of public interface											  //
	////////////////////////////////////////////////////////////////////////////

	function mapSourceFieldToField(tx_newspaper_ArticleIface $article, $field) {
		foreach ($article->getAttributeList() as $attribute) {
			if ($article->mapFieldToSourceField($attribute, $this) == $field) {
				return $attribute;
			} 
		}
		throw new tx_newspaper_IllegalUsageException("$field is not an attribute".
			" of class ".get_class($article)." mapped to ".get_class());
	}
    
    private $sourceBehavior = null; 	///< Object to delegate operations to

}
?>